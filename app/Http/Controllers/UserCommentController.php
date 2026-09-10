<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserComment;
use App\Notifications\NewProfileCommentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserCommentController extends Controller
{
    /**
     * Post a comment on a user's profile. Any authenticated user may
     * comment. A comment may optionally be a reply to a top-level comment;
     * replies are limited to one level deep.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:user_comments,id'],
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId !== null) {
            $parent = UserComment::findOrFail($parentId);

            // A reply must target a top-level comment on the same profile,
            // which keeps the thread exactly one level deep.
            abort_unless(
                $parent->profile_user_id === $user->id && $parent->parent_id === null,
                422,
            );
        }

        $comment = UserComment::create([
            'profile_user_id' => $user->id,
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'content' => $validated['content'],
        ]);

        // Notify the profile owner that their profile received a comment.
        if (! $user->is($request->user())) {
            $user->notifyNow(new NewProfileCommentNotification($comment));
        }

        // Notify the parent comment author when this is a reply.
        if ($parentId !== null && ! $parent->user->is($request->user())) {
            $parent->user->notifyNow(new NewProfileCommentNotification($comment));
        }

        return back()->with('status', 'Your comment has been posted.');
    }

    /**
     * Delete a comment. The author may delete their own comment, the user
     * whose profile the comment was left on may delete it, and staff
     * (moderators/admins) may delete any comment. The comment is
     * soft-deleted.
     */
    public function destroy(Request $request, User $user, UserComment $comment): RedirectResponse
    {
        // The comment must have been left on the profile in the URL.
        abort_unless($comment->profile_user_id === $user->id, 422);

        abort_unless(
            $request->user() && (
                $request->user()->is($comment->user)
                || $request->user()->is($user)
                || $request->user()->isStaff()
            ),
            403,
        );

        $comment->delete();

        return back()->with('status', 'The comment has been deleted.');
    }

    /**
     * Like a comment on a user's profile. Any authenticated user may like
     * any comment (including replies). Liking is idempotent: a user can
     * like a given comment at most once.
     */
    public function like(Request $request, User $user, UserComment $comment): RedirectResponse
    {
        // The comment must have been left on the profile in the URL.
        abort_unless($comment->profile_user_id === $user->id, 422);

        $request->user()->profileCommentLikes()->firstOrCreate([
            'user_comment_id' => $comment->id,
        ]);

        return back()->with('status', 'You liked this comment.');
    }

    /**
     * Unlike a comment. Unlikeing is idempotent: unliking a comment that
     * was never liked is a no-op.
     */
    public function unlike(Request $request, User $user, UserComment $comment): RedirectResponse
    {
        // The comment must have been left on the profile in the URL.
        abort_unless($comment->profile_user_id === $user->id, 422);

        $request->user()->profileCommentLikes()
            ->where('user_comment_id', $comment->id)
            ->delete();

        return back()->with('status', 'You unliked this comment.');
    }
}