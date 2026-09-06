<?php

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use App\Models\SauceRequestBookmark;
use App\Models\SauceRequestComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Viewing profiles
// ---------------------------------------------------------------------------

it('shows the authenticated user their own profile', function () {
    $user = User::factory()->create(['username' => 'sauce_hunter']);

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee('sauce_hunter')
        ->assertSee('Edit profile');
});

it('shows another user profile publicly by username', function () {
    $owner = User::factory()->create(['username' => 'owner']);
    $visitor = User::factory()->create(['username' => 'visitor']);

    $this->actingAs($visitor)
        ->get(route('profile.show', $owner->username))
        ->assertOk()
        ->assertSee('owner')
        ->assertDontSee('Edit profile');
});

it('allows guests to view a public profile', function () {
    $user = User::factory()->create(['username' => 'public_user']);

    $this->get(route('profile.show', $user->username))
        ->assertOk()
        ->assertSee('public_user');
});

it('returns 404 for an unknown username', function () {
    $this->get(route('profile.show', 'does-not-exist'))
        ->assertNotFound();
});

it('renders the bio as markdown', function () {
    $user = User::factory()->create([
        'username' => 'markdown_user',
        'description' => "# Hello\n\nThis is **bold**.",
    ]);

    $this->get(route('profile.show', $user->username))
        ->assertOk()
        ->assertSee('<h1', false)
        ->assertSee('<strong>bold</strong>', false);
});

it('shows accepted answers, followers, and following counts on the profile', function () {
    $owner = User::factory()->create(['username' => 'stats_user']);

    // Two followers follow the owner.
    $followerA = User::factory()->create();
    $followerB = User::factory()->create();
    $this->actingAs($followerA)->post(route('profile.follow', $owner));
    $this->actingAs($followerB)->post(route('profile.follow', $owner));

    // The owner follows three users.
    foreach (range(1, 3) as $i) {
        $this->actingAs($owner)->post(route('profile.follow', User::factory()->create()));
    }

    // The owner has one accepted answer (and one unaccepted answer that
    // should not count).
    $sauceRequest = makeSauceRequest($owner);
    $accepted = SauceAnswer::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $owner->id,
        'content' => 'Artist is Snale.',
    ]);
    $sauceRequest->update(['accepted_sauce' => $accepted->id]);
    SauceAnswer::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $owner->id,
        'content' => 'Not accepted.',
    ]);

    $this->get(route('profile.show', $owner->username))
        ->assertOk()
        ->assertViewHas('user', fn ($user) =>
            $user->accepted_answers_count === 1
            && $user->followers_count === 2
            && $user->follows_count === 3
        );
});

it('shows previews for the user activity sections', function () {
    $owner = User::factory()->create(['username' => 'activity_user']);

    // A published request owned by the user.
    $request = makeSauceRequest($owner, ['title' => 'My request']);

    // A sauce answer by the user.
    SauceAnswer::create([
        'sauce_request_id' => $request->id,
        'user_id' => $owner->id,
        'content' => 'Artist is Snale.',
    ]);

    // A top-level comment by the user on their own request.
    SauceRequestComment::create([
        'sauce_request_id' => $request->id,
        'user_id' => $owner->id,
        'parent_id' => null,
        'content' => 'This looks like Snale.',
    ]);

    $this->get(route('profile.show', $owner->username))
        ->assertOk()
        ->assertSee('Sauce requests')
        ->assertSee('Sauce answers')
        ->assertSee('Comments made')
        ->assertSee('My request')
        ->assertSee('Artist is Snale.')
        ->assertSee('This looks like Snale.');
});

it('keeps the profile comments section below the activity sections', function () {
    $owner = User::factory()->create(['username' => 'layout_user']);
    $viewer = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Layout request']);

    $response = $this->actingAs($viewer)->get(route('profile.show', $owner->username));

    $response->assertOk();

    // The "comments made by this user" preview should appear before the
    // profile comment form, so the profile comments section stays at the
    // bottom.
    $content = $response->getContent();
    $madeAt = strpos($content, 'Comments made');
    $formAt = strpos($content, route('profile.comments.store', $owner));

    $this->assertNotFalse($madeAt, 'The "Comments made" activity section should render.');
    $this->assertNotFalse($formAt, 'The profile comments form should render.');
    $this->assertTrue(
        $madeAt < $formAt,
        'The profile comments section should render below the activity sections.'
    );
});

// ---------------------------------------------------------------------------
// Editing profile
// ---------------------------------------------------------------------------

it('redirects guests away from the edit form', function () {
    $this->get(route('profile.edit'))
        ->assertRedirect(route('login'));
});

it('redirects guests away from the update action', function () {
    $this->put(route('profile.update'))
        ->assertRedirect(route('login'));
});

it('updates the profile description', function () {
    $user = User::factory()->create(['username' => 'editor']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'description' => 'My new bio.',
        ])
        ->assertRedirect(route('profile'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'description' => 'My new bio.',
    ]);
});

it('uploads an avatar and stores it on the public disk', function () {
    Storage::fake('public');

    $user = User::factory()->create(['username' => 'avatar_user']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ])
        ->assertRedirect(route('profile'));

    $user->refresh();

    $this->assertNotNull($user->avatar_path);
    Storage::disk('public')->assertExists($user->avatar_path);
});

it('replaces an existing avatar when a new one is uploaded', function () {
    Storage::fake('public');

    $user = User::factory()->create(['username' => 'avatar_user']);
    $oldPath = 'avatars/old.png';
    Storage::disk('public')->put($oldPath, 'old');
    $user->update(['avatar_path' => $oldPath]);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'avatar' => UploadedFile::fake()->image('new.png'),
        ])
        ->assertRedirect(route('profile'));

    $user->refresh();

    $this->assertNotEquals($oldPath, $user->avatar_path);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($user->avatar_path);
});

it('rejects an invalid avatar file', function () {
    $user = User::factory()->create(['username' => 'avatar_user']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'avatar' => UploadedFile::fake()->create('document.pdf', 100),
        ])
        ->assertSessionHasErrors('avatar');
});

it('changes the username and records the change time', function () {
    $user = User::factory()->create(['username' => 'old_name']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'username' => 'new_name',
        ])
        ->assertRedirect(route('profile'));

    $user->refresh();

    $this->assertEquals('new_name', $user->username);
    $this->assertNotNull($user->username_changed_at);
});

it('rejects a username change within the cooldown period', function () {
    $user = User::factory()->create([
        'username' => 'old_name',
        'username_changed_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'username' => 'new_name',
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();

    $this->assertEquals('old_name', $user->username);
});

it('allows a username change after the cooldown period', function () {
    $user = User::factory()->create([
        'username' => 'old_name',
        'username_changed_at' => now()->subMinutes(6),
    ]);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'username' => 'new_name',
        ])
        ->assertRedirect(route('profile'));

    $user->refresh();

    $this->assertEquals('new_name', $user->username);
});

it('rejects a username that is already taken', function () {
    User::factory()->create(['username' => 'taken_name']);
    $user = User::factory()->create(['username' => 'my_name']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'username' => 'taken_name',
        ])
        ->assertSessionHasErrors('username');
});
