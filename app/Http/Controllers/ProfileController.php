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

        // Follow state: whether the viewing user follows the profile owner.
        // Only relevant when the viewer is not the owner themselves.
        $isFollowing = false;
        if (! $isOwner && $request->user()) {
            $isFollowing = $user->followers()
                ->where('follower_id', $request->user()->id)
                ->exists();
        }

        // Profile statistics: followers, following, and the number of the
        // user's sauce answers that have been accepted as the correct answer.
        $user->loadCount([
            'followers',
            'follows',
            'sauceAnswers as accepted_answers_count' => fn ($answers) => $answers
                ->join('sauce_requests', 'sauce_requests.accepted_sauce', 'sauce_answers.id')
                ->whereNotNull('sauce_requests.accepted_sauce'),
        ]);

        // Profile comments: top-level comments with their one-level-deep
        // replies, each with like counts and whether the viewer liked them.
        $userId = $request->user()?->id;
        $user->load([
            'receivedProfileComments' => fn ($query) => $query
                ->whereNull('parent_id')
                ->withCount([
                    'likes as likes_count',
                    'likes as liked_by_me' => fn ($likes) => $likes->where('user_id', $userId),
                ]),
            'receivedProfileComments.user',
            'receivedProfileComments.replies' => fn ($query) => $query->withCount([
                'likes as likes_count',
                'likes as liked_by_me' => fn ($likes) => $likes->where('user_id', $userId),
            ]),
            'receivedProfileComments.replies.user',
        ]);

        return view('pages.profile', [
            'user' => $user,
            'isOwner' => $isOwner,
            'isFollowing' => $isFollowing,
            'isStaff' => $request->user()?->isStaff() ?? false,
            'bioHtml' => $this->renderMarkdown($user->description),
        ]);
    }

    /**
     * Show a paginated list of the users who follow the given user.
     */
    public function followers(User $user): View
    {
        $users = $user->followers()
            ->withCount('followers as followers_count')
            ->latest('follows.created_at')
            ->paginate(24);

        return view('pages.profile-followers', [
            'user' => $user,
            'users' => $users,
            'type' => 'followers',
        ]);
    }

    /**
     * Show a paginated list of the users the given user follows.
     */
    public function following(User $user): View
    {
        $users = $user->following()
            ->withCount('followers as followers_count')
            ->latest('follows.created_at')
            ->paginate(24);

        return view('pages.profile-followers', [
            'user' => $user,
            'users' => $users,
            'type' => 'following',
        ]);
    }

    /**
     * Show a paginated list of the user's sauce answers that have been
     * accepted as the correct answer on their sauce request.
     */
    public function acceptedAnswers(User $user): View
    {
        $answers = $user->sauceAnswers()
            ->select('sauce_answers.*')
            ->join('sauce_requests', 'sauce_requests.accepted_sauce', 'sauce_answers.id')
            ->whereNotNull('sauce_requests.accepted_sauce')
            ->with(['user', 'sauceRequest'])
            ->withCount('likes as likes_count')
            ->latest('sauce_answers.id')
            ->paginate(12);

        return view('pages.profile-accepted-answers', [
            'user' => $user,
            'answers' => $answers,
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
