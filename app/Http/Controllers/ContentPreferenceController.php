<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentPreferenceController extends Controller
{
    /**
     * Persist the authenticated user's explicit-content preference.
     *
     * This is the server-side counterpart to the Settings "Hide NSFW content"
     * toggle, used by the first-visit explicit-content dialog so the choice
     * survives across sessions and syncs with the account setting.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hide_nsfw' => ['required', 'boolean'],
        ]);

        $request->user()->forceFill([
            'hide_nsfw' => $validated['hide_nsfw'],
        ])->save();

        return response()->json(['hide_nsfw' => $validated['hide_nsfw']]);
    }
}