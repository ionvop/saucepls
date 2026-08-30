<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function edit(): View
    {
        return view('pages.settings', ['user' => request()->user()]);
    }

    /**
     * Update the authenticated user's settings.
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->forceFill([
            'hide_nsfw' => $validated['hide_nsfw'] ?? false,
        ])->save();

        return redirect()->route('settings')
            ->with('status', 'Settings updated.');
    }
}