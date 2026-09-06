<?php

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Following users
// ---------------------------------------------------------------------------

it('lets any authenticated user follow another user', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($follower)
        ->post(route('profile.follow', $followed))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(Follow::count())->toBe(1);
    $follow = Follow::firstOrFail();
    expect($follow->follower_id)->toBe($follower->id);
    expect($follow->followed_id)->toBe($followed->id);
});

it('rejects a user trying to follow themselves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.follow', $user))
        ->assertStatus(422);

    expect(Follow::count())->toBe(0);
});

it('does not create duplicate follows for the same pair', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($follower)
        ->post(route('profile.follow', $followed))
        ->assertRedirect();

    $this->actingAs($follower)
        ->post(route('profile.follow', $followed))
        ->assertRedirect();

    expect(Follow::count())->toBe(1);
});

it('redirects guests away from the follow route', function () {
    $followed = User::factory()->create();

    $this->post(route('profile.follow', $followed))
        ->assertRedirect(route('login'));

    expect(Follow::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Unfollowing users
// ---------------------------------------------------------------------------

it('lets a user stop following another user', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($follower)
        ->post(route('profile.follow', $followed))
        ->assertRedirect();

    $this->actingAs($follower)
        ->delete(route('profile.unfollow', $followed))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(Follow::count())->toBe(0);
});

it('makes unfollowing idempotent', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($follower)
        ->delete(route('profile.unfollow', $followed))
        ->assertRedirect();

    expect(Follow::count())->toBe(0);
});

it('redirects guests away from the unfollow route', function () {
    $followed = User::factory()->create();

    $this->delete(route('profile.unfollow', $followed))
        ->assertRedirect(route('login'));
});

it('rejects a user trying to unfollow themselves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('profile.unfollow', $user))
        ->assertStatus(422);
});

// ---------------------------------------------------------------------------
// Profile page
// ---------------------------------------------------------------------------

it('exposes the follower count and follow state on the profile page', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($follower)
        ->post(route('profile.follow', $followed))
        ->assertRedirect();

    $this->actingAs($follower)
        ->get(route('profile.show', $followed->username))
        ->assertOk()
        ->assertViewHas('isFollowing', true)
        ->assertViewHas('user', fn ($user) => $user->is($followed));

    expect($followed->fresh()->loadCount('followers')->followers_count)->toBe(1);
});

it('does not show a follow state on a guest profile page', function () {
    $user = User::factory()->create();

    $this->get(route('profile.show', $user->username))
        ->assertOk()
        ->assertViewHas('isFollowing', false);
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 20 follows per minute', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $this->actingAs($follower);

    for ($i = 0; $i < 20; $i++) {
        $this->delete(route('profile.unfollow', $followed))
            ->assertRedirect();
    }

    $this->delete(route('profile.unfollow', $followed))
        ->assertTooManyRequests();
});

it('exempts staff from the follow rate limit', function () {
    $follower = User::factory()->create(['type' => 'moderator']);
    $followed = User::factory()->create();

    $this->actingAs($follower);

    for ($i = 0; $i < 25; $i++) {
        $this->delete(route('profile.unfollow', $followed))
            ->assertRedirect();
    }
});