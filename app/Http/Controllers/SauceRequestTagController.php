<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SauceRequestTagController extends Controller
{
    public function __construct(
        private readonly TagService $tags,
    ) {}

    /**
     * Replace the full set of tags on a sauce request (any authenticated
     * user). Tags are synced to match the space-separated input.
     */
    public function update(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        $request->validate([
            'tags' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->tags->sync($sauceRequest, (string) $request->input('tags', ''), $request->user());

        return back()->with('status', 'Tags have been updated.');
    }
}
