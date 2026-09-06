<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Followers / following list pages
// ---------------------------------------------------------------------------

it('lists the users who follow a user', function () {
    $owner = User::factory()->create(['username' => 'owner']);
    $followerA = User::factory()->create(['username' => 'follower_a']);
    $followerB = User::factory()->create(['username' => 'follower_b']);

    $this->actingAs($followerA)->post(route('profile.follow', $owner));
    $this->actingAs($followerB)->post(route('profile.follow', $owner));

    $this->get(route('profile.followers', $owner))
        ->assertOk()
        ->assertSee('follower_a')
        ->assertSee('follower_b');
});

it('lists the users a user follows', function () {
    $owner = User::factory()->create(['username' => 'owner']);
    $followedA = User::factory()->create(['username' => 'followed_a']);
    $followedB = User::factory()->create(['username' => 'followed_b']);

    $this->actingAs($owner)->post(route('profile.follow', $followedA));
    $this->actingAs($owner)->post(route('profile.follow', $followedB));

    $this->get(route('profile.following', $owner))
        ->assertOk()
        ->assertSee('followed_a')
        ->assertSee('followed_b');
});

it('shows an empty state when there are no followers', function () {
    $owner = User::factory()->create(['username' => 'lonely']);

    $this->get(route('profile.followers', $owner))
        ->assertOk()
        ->assertSee('No one follows lonely yet.');
});

it('shows an empty state when the user follows no one', function () {
    $owner = User::factory()->create(['username' => 'hermit']);

    $this->get(route('profile.following', $owner))
        ->assertOk()
        ->assertSee("hermit isn't following anyone yet.", false);
});

it('allows guests to view the followers and following lists', function () {
    $owner = User::factory()->create(['username' => 'public_user']);
    $follower = User::factory()->create(['username' => 'guest_follower']);

    $this->actingAs($follower)->post(route('profile.follow', $owner));

    $this->get(route('profile.followers', $owner))
        ->assertOk()
        ->assertSee('guest_follower');

    $this->get(route('profile.following', $owner))
        ->assertOk();
});

it('returns 404 for an unknown user on the followers page', function () {
    $this->get('/u/999999/followers')
        ->assertNotFound();
});

it('returns 404 for an unknown user on the following page', function () {
    $this->get('/u/999999/following')
        ->assertNotFound();
});

it('paginates the followers list', function () {
    $owner = User::factory()->create(['username' => 'popular']);

    foreach (range(1, 30) as $i) {
        $follower = User::factory()->create(['username' => "follower_{$i}"]);
        $this->actingAs($follower)->post(route('profile.follow', $owner));
    }

    // Most recent follows first: follower_30 is on page 1 (newest), and
    // follower_1 is pushed to page 2 (oldest). Note "follower_1" is a
    // substring of follower_10..19, so assert via the page 2 lookup instead
    // of asserting its absence on page 1.
    $this->get(route('profile.followers', $owner))
        ->assertOk()
        ->assertSee('follower_30');

    $this->get(route('profile.followers', $owner, ['page' => 2]))
        ->assertOk()
        ->assertSee('follower_1');
});

it('renders links to the followers and following lists on the profile', function () {
    $owner = User::factory()->create(['username' => 'linked_user']);

    $this->get(route('profile.show', $owner->username))
        ->assertOk()
        ->assertSee(route('profile.followers', $owner))
        ->assertSee(route('profile.following', $owner));
});