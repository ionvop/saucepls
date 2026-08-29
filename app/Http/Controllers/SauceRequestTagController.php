<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SauceRequestTagController extends Controller
{
    public function __construct(
        private readonly TagService $tags,
    ) {}

    /**
     * Add one or more tags to a sauce request (any authenticated user).
     */
    public function store(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        $request->validate([
            'tags' => ['required', 'string', 'max:1000'],
        ]);

        $this->tags->add($sauceRequest, (string) $request->input('tags'), $request->user());

        return back()->with('status', 'Tags have been added.');
    }

    /**
     * Remove a single tag from a sauce request (any authenticated user).
     */
    public function destroy(Request $request, SauceRequest $sauceRequest, Tag $tag): RedirectResponse
    {
        $this->tags->remove($sauceRequest, [$tag->name], $request->user());

        return back()->with('status', 'Tag has been removed.');
    }
}
