<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use App\Models\SauceRequestTextHistory;
use App\Services\TextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SauceRequestTextHistoryController extends Controller
{
    public function __construct(
        private readonly TextService $text,
    ) {}

    /**
     * Show the extracted-text history for a sauce request.
     */
    public function index(Request $request, SauceRequest $sauceRequest): View
    {
        $sauceRequest->load(['user', 'textHistory.user']);

        return view('pages.sauce-requests.text-history', [
            'sauceRequest' => $sauceRequest,
            'history' => $sauceRequest->textHistory,
        ]);
    }

    /**
     * Restore the sauce request's extracted text to the state it was in
     * after the given history entry was applied.
     */
    public function restore(Request $request, SauceRequest $sauceRequest, SauceRequestTextHistory $textHistory): RedirectResponse
    {
        abort_unless($textHistory->sauce_request_id === $sauceRequest->id, 404);

        $this->text->restoreTo($sauceRequest, $textHistory, $request->user());

        return back()->with('status', 'The extracted text has been restored to the selected state.');
    }
}