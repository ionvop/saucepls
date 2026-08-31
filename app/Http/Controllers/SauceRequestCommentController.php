<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use App\Models\SauceRequestComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SauceRequestCommentController extends Controller
{
    /**
     * Post a comment on a published sauce request. Any authenticated user
     * may comment. A comment may optionally be a reply to a top-level
     * comment; replies are limited to one level deep.
     */
    public function store(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        // Comments are only shown on published requests, so drafts are
        // treated as if they do not exist.
        if ($sauceRequest->published_at === null) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:sauce_request_comments,id'],
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId !== null) {
            $parent = SauceRequestComment::findOrFail($parentId);

            // A reply must target a top-level comment on the same request,
            // which keeps the thread exactly one level deep.
            abort_unless(
                $parent->sauce_request_id === $sauceRequest->id && $parent->parent_id === null,
                422,
            );
        }

        SauceRequestComment::create([
            'sauce_request_id' => $sauceRequest->id,
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'content' => $validated['content'],
        ]);

        return back()->with('status', 'Your comment has been posted.');
    }

    /**
     * Delete a comment. The author may delete their own comment, and staff
     * (moderators/admins) may delete any comment. The comment is
     * soft-deleted.
     */
    public function destroy(Request $request, SauceRequest $sauceRequest, SauceRequestComment $comment): RedirectResponse
    {
        abort_unless(
            $request->user() && ($request->user()->is($comment->user) || $request->user()->isStaff()),
            403,
        );

        $comment->delete();

        return back()->with('status', 'The comment has been deleted.');
    }
}