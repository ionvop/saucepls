<?php

namespace App\Http\Controllers;

use App\Models\SauceRequest;
use App\Services\TextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SauceRequestTextController extends Controller
{
    public function __construct(
        private readonly TextService $text,
    ) {}

    /**
     * Replace the extracted text on a sauce request (any authenticated
     * user). The text is fully replaced to match the input.
     */
    public function update(Request $request, SauceRequest $sauceRequest): RedirectResponse
    {
        $request->validate([
            'text' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->text->update($sauceRequest, (string) $request->input('text', ''), $request->user());

        return back()->with('status', 'The extracted text has been updated.');
    }
}