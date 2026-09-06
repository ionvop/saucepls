<?php

use App\Models\SauceRequestBookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Sauce requests list page
// ---------------------------------------------------------------------------

it('lists only the sauce requests the user published', function () {
    $owner = User::factory()->create(['username' => 'ssm']);
    $member = User::factory()->create(['username' => 'helper']);

    $ownRequest = makeSauceRequest($member, ['title' => 'Mystery art']);
    $otherRequest = makeSauceRequest($owner, ['title' => 'Someone elses request']);

    $this->get(route('profile.requests', $member))
        ->assertOk()
        ->assertSee('1 Sauce requests')
        ->assertSee($ownRequest->title)
        ->assertDontSee($otherRequest->title);
});

it('excludes unpublished draft requests', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $draft = makeSauceRequest($member, [
        'title' => 'Draft request',
        'published_at' => null,
    ]);

    $this->get(route('profile.requests', $member))
        ->assertOk()
        ->assertSee('0 Sauce requests')
        ->assertDontSee($draft->title);
});

it('shows an empty state when the user has no sauce requests', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $this->get(route('profile.requests', $member))
        ->assertOk()
        ->assertSee('has no sauce requests yet.');
});

it('links each request to its show page', function () {
    $member = User::factory()->create(['username' => 'helper']);
    $request = makeSauceRequest($member, ['title' => 'Mystery art']);

    $this->get(route('profile.requests', $member))
        ->assertOk()
        ->assertSee(route('sauce-requests.show', $request));
});

it('allows guests to view the sauce requests list', function () {
    $member = User::factory()->create(['username' => 'helper']);
    makeSauceRequest($member);

    $this->get(route('profile.requests', $member))
        ->assertOk();
});

// ---------------------------------------------------------------------------
// Bookmarks list page
// ---------------------------------------------------------------------------

it('lists the published sauce requests the user bookmarked, newest first', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $older = makeSauceRequest(User::factory()->create(), ['title' => 'Older bookmark']);
    $newer = makeSauceRequest(User::factory()->create(), ['title' => 'Newer bookmark']);

    SauceRequestBookmark::create(['sauce_request_id' => $older->id, 'user_id' => $member->id]);
    SauceRequestBookmark::create(['sauce_request_id' => $newer->id, 'user_id' => $member->id]);

    $this->get(route('profile.bookmarks', $member))
        ->assertOk()
        ->assertSee('2 Bookmarked sauce requests')
        ->assertSeeInOrder([$newer->title, $older->title]);
});

it('excludes unpublished requests from the bookmarks list', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $draft = makeSauceRequest(User::factory()->create(), [
        'title' => 'Draft bookmark',
        'published_at' => null,
    ]);
    SauceRequestBookmark::create(['sauce_request_id' => $draft->id, 'user_id' => $member->id]);

    $this->get(route('profile.bookmarks', $member))
        ->assertOk()
        ->assertSee('0 Bookmarked sauce requests')
        ->assertDontSee($draft->title);
});

it('shows an empty state when the user has no bookmarks', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $this->get(route('profile.bookmarks', $member))
        ->assertOk()
        ->assertSee('has no bookmarked sauce requests.');
});

it('allows guests to view the bookmarks list', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $request = makeSauceRequest(User::factory()->create(), ['title' => 'Bookmarked']);
    SauceRequestBookmark::create(['sauce_request_id' => $request->id, 'user_id' => $member->id]);

    $this->get(route('profile.bookmarks', $member))
        ->assertOk();
});