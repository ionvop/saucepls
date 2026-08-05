<?php

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
