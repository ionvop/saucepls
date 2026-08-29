<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublishSauceRequestRequest;
use App\Http\Requests\StoreSauceRequestRequest;
use App\Http\Requests\UpdateSauceRequestRequest;
use App\Models\SauceRequest;
use App\Services\DuplicateDetectionService;
use App\Services\OcrService;
use App\Services\PerceptualHashService;
use App\Services\TagInferenceService;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SauceRequestController extends Controller
{
    public function __construct(
        private readonly PerceptualHashService $perceptualHash,
        private readonly DuplicateDetectionService $duplicateDetection,
        private readonly OcrService $ocr,
        private readonly TagInferenceService $tagInference,
        private readonly TagService $tags,
    ) {}

    /**
     * Show a paginated feed of sauce requests.
     */
    public function index(): View
    {
        $sauceRequests = SauceRequest::query()
            ->with('user')
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

        $imagePath = $request->file('image')->store('sauce-requests', 'public');

        $absolutePath = Storage::disk('public')->path($imagePath);

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
     */
    public function publish(PublishSauceRequestRequest $request, SauceRequest $sauceRequest): RedirectResponse
    {
        $validated = $request->validated();

        $sauceRequest->update([
            'text' => $validated['text'] ?? '',
        ]);

        $this->tags->sync($sauceRequest, (string) ($validated['tags'] ?? ''), $request->user());

        return redirect()
            ->route('sauce-requests.show', $sauceRequest)
            ->with('status', 'Your sauce request has been posted.');
    }

    /**
     * Show a single sauce request.
     */
    public function show(Request $request, SauceRequest $sauceRequest): View
    {
        $sauceRequest->load(['user', 'tags']);

        return view('pages.sauce-requests.show', [
            'sauceRequest' => $sauceRequest,
            'isOwner' => $request->user()?->is($sauceRequest->user) ?? false,
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
}
