<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSauceRequestRequest;
use App\Http\Requests\UpdateSauceRequestRequest;
use App\Models\SauceRequest;
use App\Services\OcrService;
use App\Services\PerceptualHashService;
use App\Services\TagInferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SauceRequestController extends Controller
{
    public function __construct(
        private readonly PerceptualHashService $perceptualHash,
        private readonly OcrService $ocr,
        private readonly TagInferenceService $tagInference,
    ) {
    }

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
     * Store a newly created sauce request.
     */
    public function store(StoreSauceRequestRequest $request): RedirectResponse
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

        // TODO: Persist the suggested tags once the `tags` and
        //       `sauce_request_tags` tables exist.

        return redirect()
            ->route('sauce-requests.show', $sauceRequest)
            ->with('status', 'Your sauce request has been posted.');
    }

    /**
     * Show a single sauce request.
     */
    public function show(Request $request, SauceRequest $sauceRequest): View
    {
        $sauceRequest->load('user');

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

        $sauceRequest->load('user');

        return view('pages.sauce-requests.edit', [
            'sauceRequest' => $sauceRequest,
        ]);
    }

    /**
     * Update an existing sauce request (title, description, NSFW toggle).
     */
    public function update(UpdateSauceRequestRequest $request, SauceRequest $sauceRequest): RedirectResponse
    {
        $sauceRequest->update($request->validated());

        return redirect()
            ->route('sauce-requests.show', $sauceRequest)
            ->with('status', 'Your sauce request has been updated.');
    }
}