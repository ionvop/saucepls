<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Show the subscription feed: published sauce requests from the people
     * the authenticated user follows, newest first, alongside a left panel
     * listing the followed users.
     */
    public function index(): View
    {
        $user = auth()->user();

        // The people the user follows, newest follow first, with their own
        // follower count for the left panel.
        $following = $user->following()
            ->withCount('followers as followers_count')
            ->latest('follows.created_at')
            ->get();

        // Published requests from followed users, newest first. Explicit
        // content is hidden when the user has opted out of it.
        $sauceRequests = SauceRequest::query()
            ->with('user')
            ->withCount('bookmarks as bookmarks_count')
            ->published()
            ->whereIn('user_id', $user->following()->select('followed_id'))
            ->when($user->hide_nsfw, fn ($query) => $query->where('is_explicit', false))
            ->latest('published_at')
            ->orderByDesc('id')
            ->paginate(12);

        return view('pages.subscriptions', [
            'following' => $following,
            'sauceRequests' => $sauceRequests,
        ]);
    }
}
