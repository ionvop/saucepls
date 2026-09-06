<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Follow another user.
     */
    public function follow(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422);

        $request->user()->follows()->firstOrCreate([
            'followed_id' => $user->id,
        ]);

        return back()->with('status', "You are now following {$user->username}.");
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422);

        $request->user()->follows()
            ->where('followed_id', $user->id)
            ->delete();

        return back()->with('status', "You are no longer following {$user->username}.");
    }
}