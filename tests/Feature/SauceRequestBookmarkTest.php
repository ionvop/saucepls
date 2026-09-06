<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestBookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Bookmarking sauce requests
// ---------------------------------------------------------------------------

it('lets any authenticated user bookmark a sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceRequestBookmark::count())->toBe(1);
    $bookmark = SauceRequestBookmark::firstOrFail();
    expect($bookmark->sauce_request_id)->toBe($sauceRequest->id);
    expect($bookmark->user_id)->toBe($member->id);
});

it('lets a user bookmark their own sauce request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($owner)
        ->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertRedirect();

    expect(SauceRequestBookmark::count())->toBe(1);
    expect(SauceRequestBookmark::firstOrFail()->user_id)->toBe($owner->id);
});

it('does not create duplicate bookmarks for the same user', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertRedirect();

    $this->actingAs($member)
        ->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertRedirect();

    expect(SauceRequestBookmark::count())->toBe(1);
});

it('redirects guests away from the bookmark route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertRedirect(route('login'));

    expect(SauceRequestBookmark::count())->toBe(0);
});

it('rejects bookmarks on unpublished drafts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, ['published_at' => null]);

    $this->actingAs($member)
        ->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertNotFound();

    expect(SauceRequestBookmark::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Unbookmarking sauce requests
// ---------------------------------------------------------------------------

it('lets a user remove their bookmark', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertRedirect();

    $this->actingAs($member)
        ->delete(route('sauce-requests.unbookmark', $sauceRequest))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceRequestBookmark::count())->toBe(0);
});

it('makes unbookmarking idempotent', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->delete(route('sauce-requests.unbookmark', $sauceRequest))
        ->assertRedirect();

    expect(SauceRequestBookmark::count())->toBe(0);
});

it('redirects guests away from the unbookmark route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->delete(route('sauce-requests.unbookmark', $sauceRequest))
        ->assertRedirect(route('login'));
});

it('exposes whether the current user bookmarked the request on the show page', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.bookmark', $sauceRequest))
        ->assertRedirect();

    $this->actingAs($member)
        ->get(route('sauce-requests.show', $sauceRequest))
        ->assertOk();

    $loaded = $sauceRequest->fresh()->loadCount(['bookmarks as bookmarks_count']);

    expect($loaded->bookmarks_count)->toBe(1);
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 20 bookmarks per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member);

    for ($i = 0; $i < 20; $i++) {
        $this->delete(route('sauce-requests.unbookmark', $sauceRequest))
            ->assertRedirect();
    }

    $this->delete(route('sauce-requests.unbookmark', $sauceRequest))
        ->assertTooManyRequests();
});

it('exempts staff from the bookmark rate limit', function () {
    $owner = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($moderator);

    for ($i = 0; $i < 25; $i++) {
        $this->delete(route('sauce-requests.unbookmark', $sauceRequest))
            ->assertRedirect();
    }
});