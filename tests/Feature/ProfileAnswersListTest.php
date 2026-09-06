<?php

use App\Models\SauceAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Sauce answers list page
// ---------------------------------------------------------------------------

it('lists only the sauce answers the user provided', function () {
    $member = User::factory()->create(['username' => 'helper']);
    $other = User::factory()->create(['username' => 'someone_else']);

    $request = makeSauceRequest(User::factory()->create());
    $ownAnswer = SauceAnswer::create([
        'sauce_request_id' => $request->id,
        'user_id' => $member->id,
        'content' => 'Artist is Snale.',
    ]);
    $otherAnswer = SauceAnswer::create([
        'sauce_request_id' => $request->id,
        'user_id' => $other->id,
        'content' => 'Its definitely someone else.',
    ]);

    $this->get(route('profile.answers', $member))
        ->assertOk()
        ->assertSee('1 Sauce answer')
        ->assertSee($ownAnswer->content)
        ->assertDontSee($otherAnswer->content);
});

it('shows an empty state when the user has no sauce answers', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $this->get(route('profile.answers', $member))
        ->assertOk()
        ->assertSee('has no sauce answers yet.');
});

it('links each answer to its parent sauce request', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $request = makeSauceRequest(User::factory()->create(), ['title' => 'Mystery request']);
    SauceAnswer::create([
        'sauce_request_id' => $request->id,
        'user_id' => $member->id,
        'content' => 'Artist is Snale.',
    ]);

    $this->get(route('profile.answers', $member))
        ->assertOk()
        ->assertSee(route('sauce-requests.show', $request));
});

it('allows guests to view the sauce answers list', function () {
    $member = User::factory()->create(['username' => 'helper']);
    $request = makeSauceRequest(User::factory()->create());
    SauceAnswer::create([
        'sauce_request_id' => $request->id,
        'user_id' => $member->id,
        'content' => 'Artist is Snale.',
    ]);

    $this->get(route('profile.answers', $member))
        ->assertOk();
});