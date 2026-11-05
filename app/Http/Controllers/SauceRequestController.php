<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublishSauceRequestRequest;
use App\Http\Requests\StoreSauceRequestRequest;
use App\Http\Requests\UpdateSauceRequestRequest;
use App\Models\SauceRequest;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\ImageCompressionService;
use App\Services\MarkdownService;
use App\Services\OcrService;
use App\Services\PerceptualHashService;
use App\Services\SauceNaoService;
use App\Services\TagInferenceService;
use App\Services\TagService;
use App\Services\TextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use DateTimeZone;

class SauceRequestController extends Controller
{
    public function __construct(
        private readonly PerceptualHashService $perceptualHash,
        private readonly DuplicateDetectionService $duplicateDetection,
        private readonly OcrService $ocr,
        private readonly TagInferenceService $tagInference,
        private readonly SauceNaoService $sauceNao,
        private readonly TagService $tags,
        private readonly TextService $text,
        private readonly ImageCompressionService $imageCompression,
    ) {}

    /**
     * Search published sauce requests by keyword, with a solved/unsolved
     * filter and a sort order (recent, popular, or trending).
     */
    public function search(Request $request): View
    {
        $hideNsfw = auth()->check()
            ? auth()->user()->hide_nsfw
            : (bool) $request->cookie('hide_nsfw');

        // filter: all (default) | solved | unsolved
        $filter = $request->query('filter', 'all');
        $filter = in_array($filter, ['all', 'solved', 'unsolved'], true) ? $filter : 'all';

        // sort: recent (default) | popular | trending
        $sort = $request->query('sort', 'recent');
        $sort = in_array($sort, ['recent', 'popular', 'trending'], true) ? $sort : 'recent';

        // timezone: IANA identifier sent by the browser for date prefixes.
        // Defaults to UTC when absent or invalid.
        $timezone = (string) $request->query('tz', 'UTC');
        $timezone = in_array($timezone, DateTimeZone::listIdentifiers(), true) ? $timezone : 'UTC';

        $sauceRequests = SauceRequest::query()
            ->with('user')
            ->withCount('bookmarks as bookmarks_count')
            ->published()
            ->when($hideNsfw, fn ($query) => $query->where('is_explicit', false))
            ->when(trim((string) $request->query('q')), fn ($query, $q) => $query->search($q, $timezone))
            ->when($filter === 'solved', fn ($query) => $query->solved())
            ->when($filter === 'unsolved', fn ($query) => $query->unsolved())
            ->when(
                $sort,
                fn ($query) => match ($sort) {
                    'popular' => $query->popular(),
                    'trending' => $query->trending(),
                    default => $query->latest('published_at')->orderByDesc('id'),
                },
            )
            ->paginate(12);

        return view('pages.search', [
            'sauceRequests' => $sauceRequests,
            'q' => (string) $request->query('q'),
            'filter' => $filter,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form to create a new sauce request.
     */
    public function create(): View
    {
        $this->purgeAbandonedDrafts(auth()->user());

        return view('pages.sauce-requests.create');
    }

    /**
        * Store the uploaded image as a draft, check for duplicates and run
        * SauceNAO before deferring OCR and tag inference until continuation.
     */
    public function upload(StoreSauceRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Clear the user's abandoned drafts before creating a new one so
        // they do not accumulate as hidden unpublished rows.
        $this->purgeAbandonedDrafts($request->user());

        $imagePath = $request->file('image')->store('sauce-requests', 'public');

        $absolutePath = Storage::disk('public')->path($imagePath);

        // GIFs are stored as-is to preserve their animation and skip the
        // entire pre-post pipeline (perceptual hashing, OCR, tag
        // inference, duplicate detection, and SauceNAO), which cannot
        // process them. They move straight to the details page.
        if ($this->imageCompression->isGif($absolutePath)) {
            $sauceRequest = SauceRequest::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'] ?? 'Sauce pls',
                'description' => $validated['description'] ?? '',
                'text' => '',
                'image_path' => $imagePath,
                'phash64' => null,
                'is_explicit' => $validated['is_explicit'] ?? true,
                'enriched_at' => now(),
            ]);

            return redirect()->route('sauce-requests.details', $sauceRequest);
        }

        // Compress the image to WebP. Every non-GIF image is converted so
        // all stored images are WebP under the target size. The original
        // is discarded and replaced in place, so the pre-post pipeline
        // below runs against the compressed image.
        $this->imageCompression->compressToWebpUnder($absolutePath);

        $phash = $this->perceptualHash->hash($absolutePath);

        $sauceRequest = SauceRequest::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'Sauce pls',
            'description' => $validated['description'] ?? '',
            'text' => '',
            'image_path' => $imagePath,
            'phash64' => $phash,
            'is_explicit' => true,
        ]);

        // Check for near-duplicates before making the slower external lookups.
        $duplicate = $this->duplicateDetection->findDuplicate($phash, $sauceRequest->id);

        if ($duplicate !== null) {
            return redirect()
                ->route('sauce-requests.duplicate', [$sauceRequest, 'duplicate' => $duplicate]);
        }

        $matches = $this->lookupSauceNao($sauceRequest);

        if ($matches !== []) {
            return redirect()
                ->route('sauce-requests.sauce', $sauceRequest);
        }

        $this->enrichDraft($sauceRequest);

        return redirect()
            ->route('sauce-requests.details', $sauceRequest);
    }

    /**
     * Continue past a possible duplicate and run SauceNAO before enrichment.
     */
    public function continueAfterDuplicate(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        abort_unless($request->user()?->is($sauceRequest->user), 403);

        $matches = $this->lookupSauceNao($sauceRequest);

        if ($matches !== []) {
            return redirect()->route('sauce-requests.sauce', $sauceRequest);
        }

        $this->enrichDraft($sauceRequest);

        return redirect()->route('sauce-requests.details', $sauceRequest);
    }

    /**
     * Continue past a SauceNAO match and prepare the details page.
     */
    public function continueAfterSauce(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        abort_unless($request->user()?->is($sauceRequest->user), 403);

        $this->enrichDraft($sauceRequest);

        return redirect()->route('sauce-requests.details', $sauceRequest);
    }

    /**
     * Show the intermediate page when the uploaded image is a
     * near-duplicate of an existing sauce request.
     */
    public function duplicate(Request $request, SauceRequest $sauceRequest, SauceRequest $duplicate): View
    {
        abort_unless($request->user()?->is($sauceRequest->user), 403);

        $sauceRequest->load(['user', 'tags']);
        $duplicate->load(['user', 'tags']);

        return view('pages.sauce-requests.duplicate', [
            'sauceRequest' => $sauceRequest,
            'duplicate' => $duplicate,
        ]);
    }

    /**
     * Show the intermediate page when SauceNAO identifies the uploaded
     * image. The user can view the result or continue posting anyway.
     */
    public function sauce(Request $request, SauceRequest $sauceRequest): View
    {
        abort_unless($request->user()?->is($sauceRequest->user), 403);

        $sauceRequest->load(['user', 'tags']);

        // The lookup result is cached when the image is searched. If it is
        // missing (e.g. the cache was cleared), re-run the lookup so the
        // page still has something to show.
        $matches = $this->lookupSauceNao($sauceRequest, retryOnCacheMiss: true);

        return view('pages.sauce-requests.sauce', [
            'sauceRequest' => $sauceRequest,
            'matches' => $matches ?? [],
        ]);
    }

    /**
     * Show the details page where the user can review and edit the OCR
     * text and the tags suggested by the inference pipeline before posting.
     */
    public function details(Request $request, SauceRequest $sauceRequest): View
    {
        abort_unless($request->user()?->is($sauceRequest->user), 403);

        if ($sauceRequest->published_at === null) {
            $this->enrichDraft($sauceRequest);
            $sauceRequest->refresh();
        }

        $sauceRequest->load(['user', 'tags']);

        return view('pages.sauce-requests.details', [
            'sauceRequest' => $sauceRequest,
        ]);
    }

    /**
     * Publish a draft sauce request with the user's final text and tags.
     *
     * This is the only action that actually "posts" a sauce request. Until
     * this runs, the request is an unpublished draft that is hidden from
     * the public feed.
     */
    public function publish(PublishSauceRequestRequest $request, SauceRequest $sauceRequest): RedirectResponse
    {
        $validated = $request->validated();

        // Set the final text (recording a history entry if it changed from
        // the draft) and publish the request.
        $this->text->update($sauceRequest, (string) ($validated['text'] ?? ''), $request->user());

        $sauceRequest->update([
            'published_at' => now(),
            // The explicit toggle now lives on the details (publish) page.
            // When the checkbox is absent the draft's inferred value is kept.
            'is_explicit' => $validated['is_explicit'] ?? $sauceRequest->is_explicit,
        ]);

        $this->tags->sync($sauceRequest, (string) ($validated['tags'] ?? ''), $request->user());

        return redirect()
            ->route('sauce-requests.show', $sauceRequest)
            ->with('status', 'Your sauce request has been posted.');
    }

    /**
     * Cancel an unpublished draft sauce request. The draft is soft-deleted
     * and the user is returned to the upload form.
     */
    public function cancel(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        abort_unless($request->user()?->is($sauceRequest->user), 403);

        $sauceRequest->delete();

        return redirect()
            ->route('create')
            ->with('status', 'Your sauce request has been canceled.');
    }

    /**
     * Show a single sauce request. Unpublished drafts are only visible to
     * their owner (e.g. to preview before posting).
     */
    public function show(Request $request, SauceRequest $sauceRequest): View
    {
        if ($sauceRequest->published_at === null && ! $request->user()?->is($sauceRequest->user)) {
            abort(404);
        }

        $userId = $request->user()?->id;

        // Answers sort by likes by default, with an option to sort by most
        // recent. The accepted answer is always pinned to the top.
        $sort = $request->query('sort', 'likes');
        $sort = in_array($sort, ['likes', 'recent'], true) ? $sort : 'likes';

        $sauceRequest->load([
            'user',
            'tags',
            'comments' => fn ($query) => $query->withCount([
                'likes as likes_count',
                'likes as liked_by_me' => fn ($likes) => $likes->where('user_id', $userId),
            ]),
            'comments.user',
            'comments.replies' => fn ($query) => $query->withCount([
                'likes as likes_count',
                'likes as liked_by_me' => fn ($likes) => $likes->where('user_id', $userId),
            ]),
            'comments.replies.user',
            'answers' => fn ($query) => $query
                ->withCount([
                    'likes as likes_count',
                    'likes as liked_by_me' => fn ($likes) => $likes->where('user_id', $userId),
                ])
                ->with([
                    'comments' => fn ($comments) => $comments->withCount([
                        'likes as likes_count',
                        'likes as liked_by_me' => fn ($likes) => $likes->where('user_id', $userId),
                    ]),
                    'comments.user',
                ])
                ->when($sort === 'recent', fn ($q) => $q->latest('id'))
                ->when($sort === 'likes', fn ($q) => $q->orderByDesc('likes_count')->orderByDesc('id')),
            'answers.user',
        ]);

        // Pin the accepted answer to the top of the list regardless of the
        // active sort, so the solved answer is always the first thing seen.
        if ($sauceRequest->accepted_sauce !== null) {
            $sauceRequest->setRelation(
                'answers',
                $sauceRequest->answers->sortByDesc(fn ($answer) => $answer->id === $sauceRequest->accepted_sauce)->values(),
            );
        }

        // Pre-render the description and each answer's content as safe Markdown
        // HTML so the views can echo them with {!! !!}.
        $descriptionHtml = MarkdownService::render($sauceRequest->description ?? '');
        $sauceRequest->answers->each(function ($answer) {
            $answer->content_html = MarkdownService::render($answer->content ?? '');
        });

        // Bookmark stats for the request: bookmarked_by_me so the view can
        // render the toggle state, and bookmarks_count for the total count.
        $sauceRequest->loadCount([
            'bookmarks as bookmarks_count',
            'bookmarks as bookmarked_by_me' => fn ($query) => $query->where('user_id', $userId),
        ]);

        return view('pages.sauce-requests.show', [
            'sauceRequest' => $sauceRequest,
            'descriptionHtml' => $descriptionHtml,
            'isOwner' => $request->user()?->is($sauceRequest->user) ?? false,
            'isStaff' => $request->user()?->isStaff() ?? false,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form to edit an existing sauce request.
     */
    public function edit(Request $request, SauceRequest $sauceRequest): View
    {
        abort_unless($request->user()?->is($sauceRequest->user), 403);

        $sauceRequest->load(['user', 'tags']);

        return view('pages.sauce-requests.edit', [
            'sauceRequest' => $sauceRequest,
        ]);
    }

    /**
     * Update an existing sauce request (title, description, NSFW toggle).
     */
    public function update(UpdateSauceRequestRequest $request, SauceRequest $sauceRequest): RedirectResponse
    {
        $validated = $request->validated();

        $sauceRequest->update([
            'title' => $validated['title'] ?? 'Sauce pls',
            'description' => $validated['description'] ?? '',
            'is_explicit' => $validated['is_explicit'] ?? false,
        ]);

        return redirect()
            ->route('sauce-requests.show', $sauceRequest)
            ->with('status', 'Your sauce request has been updated.');
    }

    /**
     * Delete a sauce request. Owners may delete their own requests, and
     * staff (moderators/admins) may delete any request. The request is
     * soft-deleted and its uploaded image file is removed.
     */
    public function destroy(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        abort_unless(
            $request->user() && ($request->user()->is($sauceRequest->user) || $request->user()->isStaff()),
            403,
        );

        if ($sauceRequest->image_path) {
            Storage::disk('public')->delete($sauceRequest->image_path);
        }

        $sauceRequest->delete();

        return redirect()
            ->route('search')
            ->with('status', 'Your sauce request has been deleted.');
    }

    /**
     * Bookmark a sauce request so the user can track its progress.
     *
     * Bookmarks are only shown on published requests, so drafts are treated
     * as if they do not exist.
     */
    public function bookmark(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        $request->user()->bookmarks()->firstOrCreate([
            'sauce_request_id' => $sauceRequest->id,
        ]);

        return back()->with('status', 'You bookmarked this sauce request.');
    }

    /**
     * Remove the user's bookmark from a sauce request.
     *
     * Bookmarks are only shown on published requests, so drafts are treated
     * as if they do not exist.
     */
    public function unbookmark(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        $request->user()->bookmarks()
            ->where('sauce_request_id', $sauceRequest->id)
            ->delete();

        return back()->with('status', 'You removed your bookmark.');
    }

    /**
     * The cache key used to store the SauceNAO lookup results for a draft.
     */
    private function sauceCacheKey(SauceRequest $sauceRequest): string
    {
        return "sauce-requests.{$sauceRequest->id}.saucenao";
    }

    /**
     * Run SauceNAO once for a draft and cache its results for the result page.
     *
     * @return array<int, array<string, mixed>>
     */
    private function lookupSauceNao(SauceRequest $sauceRequest, bool $retryOnCacheMiss = false): array
    {
        $cacheKey = $this->sauceCacheKey($sauceRequest);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        if ($sauceRequest->saucenao_checked_at !== null && ! $retryOnCacheMiss) {
            return [];
        }

        if (! $sauceRequest->image_path) {
            return [];
        }

        $absolutePath = Storage::disk('public')->path($sauceRequest->image_path);
        $matches = $this->sauceNao->lookup($absolutePath);

        Cache::put(
            $cacheKey,
            $matches,
            now()->addMinutes((int) config('services.saucenao.cache_ttl', 10)),
        );

        $sauceRequest->forceFill(['saucenao_checked_at' => now()])->save();

        return $matches;
    }

    /**
     * Populate OCR text and inferred tags once, after the user continues.
     */
    private function enrichDraft(SauceRequest $sauceRequest): void
    {
        if ($sauceRequest->enriched_at !== null || ! $sauceRequest->image_path) {
            return;
        }

        $lock = Cache::lock("sauce-requests.{$sauceRequest->id}.enrichment", 120);

        $lock->block(120, function () use ($sauceRequest): void {
            $draft = SauceRequest::query()->findOrFail($sauceRequest->id);

            if ($draft->enriched_at !== null) {
                return;
            }

            $absolutePath = Storage::disk('public')->path($draft->image_path);

            if ($this->imageCompression->isGif($absolutePath)) {
                $draft->forceFill(['enriched_at' => now()])->save();

                return;
            }

            $text = $this->ocr->extractText($absolutePath);
            $inference = $this->tagInference->inferWithRating($absolutePath);

            $this->tags->addInferred($draft, (array) $inference['tags']);

            $updates = ['enriched_at' => now()];

            if ($draft->text === '' || $draft->text === null) {
                $updates['text'] = $text;
            }

            if ($inference['rating'] === 'safe'
                && $inference['rating_confidence'] !== null
                && $inference['rating_confidence'] >= (float) config('services.tag_inference.threshold', 0.2)) {
                $updates['is_explicit'] = false;
            }

            $draft->forceFill($updates)->save();
        });
    }

    /**
     * Soft-delete the given user's unpublished drafts that are older than
     * the configured TTL, and remove their uploaded image files. This runs
     * opportunistically when the user starts a new upload or visits the
     * upload form, so abandoned drafts do not accumulate.
     */
    private function purgeAbandonedDrafts(?User $user): void
    {
        if ($user === null) {
            return;
        }

        $cutoff = now()->subHours((int) config('services.drafts.ttl_hours', 24));

        $drafts = SauceRequest::query()
            ->where('user_id', $user->id)
            ->whereNull('published_at')
            ->where('created_at', '<', $cutoff)
            ->get();

        foreach ($drafts as $draft) {
            if ($draft->image_path) {
                Storage::disk('public')->delete($draft->image_path);
            }

            $draft->delete();
        }
    }
}
