<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use App\Models\SauceRequestTaggingHistory;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SauceRequestTagHistoryController extends Controller
{
    public function __construct(
        private readonly TagService $tags,
    ) {}

    /**
     * Show the tagging history for a sauce request.
     */
    public function index(Request $request, SauceRequest $sauceRequest): View
    {
        $sauceRequest->load(['user', 'tags', 'taggingHistory.user']);

        return view('pages.sauce-requests.tag-history', [
            'sauceRequest' => $sauceRequest,
            'history' => $sauceRequest->taggingHistory,
        ]);
    }

    /**
     * Restore the sauce request's tags to the state they were in after the
     * given history entry was applied.
     */
    public function restore(Request $request, SauceRequest $sauceRequest, SauceRequestTaggingHistory $taggingHistory): RedirectResponse
    {
        abort_unless($taggingHistory->sauce_request_id === $sauceRequest->id, 404);

        $this->tags->restoreTo($sauceRequest, $taggingHistory, $request->user());

        return back()->with('status', 'The tags have been restored to the selected state.');
    }
}