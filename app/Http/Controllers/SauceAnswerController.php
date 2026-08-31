<?php

namespace App\Http\Controllers;

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SauceAnswerController extends Controller
{
    /**
     * Provide a sauce answer on a published sauce request. Any
     * authenticated user may answer. An answer consists of the sauce
     * itself plus an optional link to the source.
     */
    public function store(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        // Answers are only shown on published requests, so drafts are
        // treated as if they do not exist.
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'url' => ['nullable', 'string', 'url', 'max:2048'],
        ]);

        SauceAnswer::create([
            'sauce_request_id' => $sauceRequest->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'url' => $validated['url'] ?? null,
        ]);

        return back()->with('status', 'Your answer has been posted.');
    }

    /**
     * Delete a sauce answer. The author may delete their own answer, and
     * staff (moderators/admins) may delete any answer. The answer is
     * soft-deleted. If the deleted answer was the accepted one, the
     * request becomes unsolved again.
     */
    public function destroy(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer): RedirectResponse
    {
        abort_unless(
            $request->user() && ($request->user()->is($answer->user) || $request->user()->isStaff()),
            403,
        );

        // The answer must belong to the sauce request in the URL.
        abort_unless($answer->sauce_request_id === $sauceRequest->id, 422);

        $answer->delete();

        // If the deleted answer was the accepted one, clear it so the
        // request is no longer marked as solved.
        if ($sauceRequest->accepted_sauce === $answer->id) {
            $sauceRequest->update(['accepted_sauce' => null]);
        }

        return back()->with('status', 'The answer has been deleted.');
    }

    /**
     * Like a sauce answer on a published sauce request. Any authenticated
     * user may like any answer. Liking is idempotent: a user can like a
     * given answer at most once.
     */
    public function like(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer): RedirectResponse
    {
        // Likes are only shown on published requests, so drafts are treated
        // as if they do not exist.
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        // The answer must belong to the sauce request in the URL.
        abort_unless($answer->sauce_request_id === $sauceRequest->id, 422);

        $request->user()->sauceAnswerLikes()->firstOrCreate([
            'sauce_answer_id' => $answer->id,
        ]);

        return back()->with('status', 'You liked this answer.');
    }

    /**
     * Unlike a sauce answer. Unlikeing is idempotent: unliking an answer
     * that was never liked is a no-op.
     */
    public function unlike(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer): RedirectResponse
    {
        // Likes are only shown on published requests, so drafts are treated
        // as if they do not exist.
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        // The answer must belong to the sauce request in the URL.
        abort_unless($answer->sauce_request_id === $sauceRequest->id, 422);

        $request->user()->sauceAnswerLikes()
            ->where('sauce_answer_id', $answer->id)
            ->delete();

        return back()->with('status', 'You unliked this answer.');
    }

    /**
     * Accept a sauce answer as the correct one. The author of the sauce
     * request and staff (moderators/admins) may accept any answer. The
     * accepted answer is pinned to the top of the answers list.
     */
    public function accept(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer): RedirectResponse
    {
        abort_unless(
            $request->user() && ($request->user()->is($sauceRequest->user) || $request->user()->isStaff()),
            403,
        );

        // The answer must belong to the sauce request in the URL.
        abort_unless($answer->sauce_request_id === $sauceRequest->id, 422);

        $sauceRequest->update(['accepted_sauce' => $answer->id]);

        return back()->with('status', 'You accepted this answer.');
    }

    /**
     * Un-accept the accepted sauce answer, marking the request as unsolved
     * again. The author of the sauce request and staff may un-accept.
     */
    public function unaccept(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer): RedirectResponse
    {
        abort_unless(
            $request->user() && ($request->user()->is($sauceRequest->user) || $request->user()->isStaff()),
            403,
        );

        // The answer must belong to the sauce request in the URL.
        abort_unless($answer->sauce_request_id === $sauceRequest->id, 422);

        // Only clear the accepted answer if this is the one currently
        // accepted, so un-accepting a non-accepted answer is a no-op.
        if ($sauceRequest->accepted_sauce === $answer->id) {
            $sauceRequest->update(['accepted_sauce' => null]);
        }

        return back()->with('status', 'You un-accepted this answer.');
    }
}
