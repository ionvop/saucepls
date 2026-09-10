<?php

namespace App\Http\Controllers;

use App\Models\SauceAnswer;
use App\Models\SauceAnswerComment;
use App\Models\SauceRequest;
use App\Notifications\NewAnswerCommentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SauceAnswerCommentController extends Controller
{
    /**
     * Post a comment on a sauce answer. Any authenticated user may
     * comment. Comments are flat replies, so there is no nesting.
     */
    public function store(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer): RedirectResponse
    {
        // Comments are only shown on published requests, so drafts are
        // treated as if they do not exist.
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        // The answer must belong to the sauce request in the URL.
        abort_unless($answer->sauce_request_id === $sauceRequest->id, 422);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $comment = SauceAnswerComment::create([
            'sauce_answer_id' => $answer->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        // Notify the answer author that their answer received a comment.
        if (! $answer->user->is($request->user())) {
            $answer->user->notifyNow(new NewAnswerCommentNotification($comment));
        }

        // Notify the request author that an answer on their request received
        // a comment.
        if (! $sauceRequest->user->is($request->user())) {
            $sauceRequest->user->notifyNow(new NewAnswerCommentNotification($comment));
        }

        return back()->with('status', 'Your comment has been posted.');
    }

    /**
     * Delete a comment. The author may delete their own comment, and staff
     * (moderators/admins) may delete any comment. The comment is
     * soft-deleted.
     */
    public function destroy(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer, SauceAnswerComment $comment): RedirectResponse
    {
        abort_unless(
            $request->user() && ($request->user()->is($comment->user) || $request->user()->isStaff()),
            403,
        );

        // The comment must belong to the answer in the URL, which in turn
        // must belong to the sauce request in the URL.
        abort_unless(
            $comment->sauce_answer_id === $answer->id && $answer->sauce_request_id === $sauceRequest->id,
            422,
        );

        $comment->delete();

        return back()->with('status', 'The comment has been deleted.');
    }

    /**
     * Like a comment on a sauce answer. Any authenticated user may like
     * any comment. Liking is idempotent: a user can like a given comment
     * at most once.
     */
    public function like(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer, SauceAnswerComment $comment): RedirectResponse
    {
        // Likes are only shown on published requests, so drafts are treated
        // as if they do not exist.
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        // The comment must belong to the answer in the URL, which in turn
        // must belong to the sauce request in the URL.
        abort_unless(
            $comment->sauce_answer_id === $answer->id && $answer->sauce_request_id === $sauceRequest->id,
            422,
        );

        $request->user()->answerCommentLikes()->firstOrCreate([
            'sauce_answer_comment_id' => $comment->id,
        ]);

        return back()->with('status', 'You liked this comment.');
    }

    /**
     * Unlike a comment. Unlikeing is idempotent: unliking a comment that
     * was never liked is a no-op.
     */
    public function unlike(Request $request, SauceRequest $sauceRequest, SauceAnswer $answer, SauceAnswerComment $comment): RedirectResponse
    {
        // Likes are only shown on published requests, so drafts are treated
        // as if they do not exist.
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        // The comment must belong to the answer in the URL, which in turn
        // must belong to the sauce request in the URL.
        abort_unless(
            $comment->sauce_answer_id === $answer->id && $answer->sauce_request_id === $sauceRequest->id,
            422,
        );

        $request->user()->answerCommentLikes()
            ->where('sauce_answer_comment_id', $comment->id)
            ->delete();

        return back()->with('status', 'You unliked this comment.');
    }
}