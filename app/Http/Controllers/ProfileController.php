<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;

class ProfileController extends Controller
{
    /**
     * Show a user's profile. When no username is given, show the
     * authenticated user's own profile.
     */
    public function show(Request $request, ?string $username = null): View|RedirectResponse
    {
        $user = $username
            ? User::query()->where('username', $username)->firstOrFail()
            : $request->user();

        // A guest visiting /profile (without a username) has no profile to show.
        if (! $user) {
            return redirect()->route('login');
        }

        $isOwner = $request->user()?->is($user) ?? false;

        return view('pages.profile', [
            'user' => $user,
            'isOwner' => $isOwner,
            'bioHtml' => $this->renderMarkdown($user->description),
        ]);
    }

    /**
     * Show the edit form for the authenticated user's own profile.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('pages.profile-edit', [
            'user' => $user,
            'canChangeUsername' => $user->canChangeUsername(),
            'usernameAvailableAt' => $user->usernameChangeAvailableAt(),
        ]);
    }

    /**
     * Update the authenticated user's profile (avatar, description, username).
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Avatar upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        // Description
        if (array_key_exists('description', $validated)) {
            $user->description = $validated['description'];
        }

        // Username (respect the cooldown, and only record a change when it differs)
        if (
            array_key_exists('username', $validated)
            && $user->canChangeUsername()
            && $validated['username'] !== $user->username
        ) {
            $user->username = $validated['username'];
            $user->username_changed_at = now();
        }

        $user->save();

        return redirect()
            ->route('profile')
            ->with('status', 'Profile updated successfully.');
    }

    /**
     * Render a Markdown string to safe HTML.
     */
    private function renderMarkdown(string $markdown): string
    {
        try {
            $converter = new CommonMarkConverter([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ]);

            return $converter->convert($markdown)->getContent();
        } catch (CommonMarkException) {
            return e($markdown);
        }
    }
}
