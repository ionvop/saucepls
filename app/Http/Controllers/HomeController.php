<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the home page.
     *
     * Guests see a hero plus the Popular this month, Trending, and Recent
     * sections. Authenticated users see a Subscription feed (posts from the
     * people they follow) followed by the same three sections.
     */
    public function index(Request $request): View
    {
        $hideNsfw = auth()->check()
            ? auth()->user()->hide_nsfw
            : (bool) $request->cookie('hide_nsfw');

        $isAuthenticated = auth()->check();

        // Popular this month: most bookmarked requests published within the
        // past month.
        $popularThisMonth = $this->baseQuery($hideNsfw)
            ->where('published_at', '>=', now()->subMonth())
            ->popular()
            ->limit(8)
            ->get();

        // Trending: most bookmarked within the past week.
        $trending = $this->baseQuery($hideNsfw)
            ->trending()
            ->limit(8)
            ->get();

        // Recent: newest published requests first.
        $recent = $this->baseQuery($hideNsfw)
            ->latest('published_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        // Subscription feed: published requests from the people the user
        // follows, newest first. Only built for authenticated users.
        $subscriptionFeed = $isAuthenticated
            ? $this->baseQuery($hideNsfw)
                ->whereIn('user_id', auth()->user()->following()->select('followed_id'))
                ->latest('published_at')
                ->orderByDesc('id')
                ->limit(8)
                ->get()
            : collect();

        return view('pages.home', [
            'isAuthenticated' => $isAuthenticated,
            'popularThisMonth' => $popularThisMonth,
            'trending' => $trending,
            'recent' => $recent,
            'subscriptionFeed' => $subscriptionFeed,
        ]);
    }

    /**
     * The shared query for every home page section: published sauce requests
     * with their author and bookmark count, optionally hiding explicit
     * content.
     */
    private function baseQuery(?bool $hideNsfw): Builder
    {
        return SauceRequest::query()
            ->with('user')
            ->withCount('bookmarks as bookmarks_count')
            ->published()
            ->when($hideNsfw ?? false, fn ($query) => $query->where('is_explicit', false));
    }
}