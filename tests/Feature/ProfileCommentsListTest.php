<?php

use App\Models\SauceRequestComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Comments list page
// ---------------------------------------------------------------------------

it('lists only the top-level comments the user wrote', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $request = makeSauceRequest(User::factory()->create());

    $topLevel = SauceRequestComment::create([
        'sauce_request_id' => $request->id,
        'user_id' => $member->id,
        'parent_id' => null,
        'content' => 'This looks like Snale.',
    ]);
    // A reply by the same user should be excluded.
    SauceRequestComment::create([
        'sauce_request_id' => $request->id,
        'user_id' => $member->id,
        'parent_id' => $topLevel->id,
        'content' => 'Agreed, definitely Snale.',
    ]);

    $this->get(route('profile.comments', $member))
        ->assertOk()
        ->assertSee('1 Comment')
        ->assertSee('This looks like Snale.')
        ->assertDontSee('Agreed, definitely Snale.');
});

it('shows an empty state when the user has no comments', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $this->get(route('profile.comments', $member))
        ->assertOk()
        ->assertSee('has no comments yet.');
});

it('links each comment to its parent sauce request', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $request = makeSauceRequest(User::factory()->create(), ['title' => 'Mystery request']);
    SauceRequestComment::create([
        'sauce_request_id' => $request->id,
        'user_id' => $member->id,
        'parent_id' => null,
        'content' => 'This looks like Snale.',
    ]);

    $this->get(route('profile.comments', $member))
        ->assertOk()
        ->assertSee(route('sauce-requests.show', $request));
});

it('allows guests to view the comments list', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $request = makeSauceRequest(User::factory()->create());
    SauceRequestComment::create([
        'sauce_request_id' => $request->id,
        'user_id' => $member->id,
        'parent_id' => null,
        'content' => 'This looks like Snale.',
    ]);

    $this->get(route('profile.comments', $member))
        ->assertOk();
});