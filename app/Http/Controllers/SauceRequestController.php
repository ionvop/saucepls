<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublishSauceRequestRequest;
use App\Http\Requests\StoreSauceRequestRequest;
use App\Http\Requests\UpdateSauceRequestRequest;
use App\Models\SauceRequest;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\ImageCompressionService;
use App\Services\OcrService;
use App\Services\PerceptualHashService;
use App\Services\SauceNaoService;
use App\Services\TagInferenceService;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SauceRequestController extends Controller
{
    public function __construct(
        private readonly PerceptualHashService $perceptualHash,
        private readonly DuplicateDetectionService $duplicateDetection,
        private readonly OcrService $ocr,
        private readonly TagInferenceService $tagInference,
        private readonly SauceNaoService $sauceNao,
        private readonly TagService $tags,
        private readonly ImageCompressionService $imageCompression,
    ) {}

    /**
     * Show a paginated feed of published sauce requests.
     */
    public function index(Request $request): View
    {
        $hideNsfw = auth()->check()
            ? auth()->user()->hide_nsfw
            : (bool) $request->cookie('hide_nsfw');

        $sauceRequests = SauceRequest::query()
            ->with('user')
            ->published()
            ->when($hideNsfw, fn ($query) => $query->where('is_explicit', false))
            ->latest()
            ->paginate(12);

        return view('pages.sauce-requests.index', [
            'sauceRequests' => $sauceRequests,
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
     * Store the uploaded image as a draft sauce request and run the
     * pre-post pipeline (steps 3 and 4: OCR text + tag inference) to
     * supply the initial values for the details page.
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
            ]);

            return redirect()->route('sauce-requests.details', $sauceRequest);
        }

        // Compress the image to WebP. Every non-GIF image is converted so
        // all stored images are WebP under the target size. The original
        // is discarded and replaced in place, so the pre-post pipeline
        // below runs against the compressed image.
        $this->imageCompression->compressToWebpUnder($absolutePath);

        // Run the pre-post pipeline. Each step is currently stubbed and
        // will be replaced with real implementations later.
        $phash = $this->perceptualHash->hash($absolutePath);
        $text = $this->ocr->extractText($absolutePath);
        $suggestedTags = $this->tagInference->infer($absolutePath);

        $sauceRequest = SauceRequest::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'Sauce pls',
            'description' => $validated['description'] ?? '',
            'text' => $text,
            'image_path' => $imagePath,
            'phash64' => $phash,
            'is_explicit' => $validated['is_explicit'] ?? true,
        ]);

        // Persist the tags suggested by the model inference pipeline so
        // they can be reviewed and edited on the details page.
        $this->tags->sync($sauceRequest, (array) $suggestedTags, $request->user());

        // Step 1 of the pre-post pipeline: reverse image search. If the
        // image is a near-duplicate of an existing sauce request, send
        // the user to an intermediate page where they can view the
        // existing request or continue anyway. The draft just created is
        // excluded so the upload is not flagged as a duplicate of itself.
        $duplicate = $this->duplicateDetection->findDuplicate($phash, $sauceRequest->id);

        if ($duplicate !== null) {
            return redirect()
                ->route('sauce-requests.duplicate', [$sauceRequest, 'duplicate' => $duplicate]);
        }

        // Step 2 of the pre-post pipeline: SauceNAO reverse image search.
        // If the image is easily identifiable, send the user to an
        // intermediate page where they can view the result or continue
        // anyway. The result is cached so the intermediate page does not
        // need to re-hit the SauceNAO API.
        $matches = $this->sauceNao->lookup($absolutePath);

        if ($matches !== []) {
            Cache::put(
                $this->sauceCacheKey($sauceRequest),
                $matches,
                now()->addMinutes((int) config('services.saucenao.cache_ttl', 10)),
            );

            return redirect()
                ->route('sauce-requests.sauce', $sauceRequest);
        }

        return redirect()
            ->route('sauce-requests.details', $sauceRequest);
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

        // The lookup result is cached during upload. If it is missing
        // (e.g. the cache was cleared), re-run the lookup against the
        // stored image so the page still has something to show.
        $matches = Cache::get($this->sauceCacheKey($sauceRequest));

        if ($matches === null && $sauceRequest->image_path) {
            $absolutePath = Storage::disk('public')->path($sauceRequest->image_path);
            $matches = $this->sauceNao->lookup($absolutePath);
        }

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

        $sauceRequest->update([
            'text' => $validated['text'] ?? '',
            'published_at' => now(),
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

        $sauceRequest->load(['user', 'tags']);

        return view('pages.sauce-requests.show', [
            'sauceRequest' => $sauceRequest,
            'isOwner' => $request->user()?->is($sauceRequest->user) ?? false,
            'isStaff' => $request->user()?->isStaff() ?? false,
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
            'is_explicit' => $validated['is_explicit'] ?? true,
        ]);

        $this->tags->sync($sauceRequest, (string) ($validated['tags'] ?? ''), $request->user());

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
            ->route('sauce-requests.index')
            ->with('status', 'Your sauce request has been deleted.');
    }

    /**
     * The cache key used to store the SauceNAO lookup results for a draft.
     */
    private function sauceCacheKey(SauceRequest $sauceRequest): string
    {
        return "sauce-requests.{$sauceRequest->id}.saucenao";
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
