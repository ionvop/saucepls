<?php

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Accepted sauce answers list page
// ---------------------------------------------------------------------------

function makeAnswerOnSauceRequest(SauceRequest $sauceRequest, User $user): SauceAnswer
{
    return SauceAnswer::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $user->id,
        'content' => 'Artist is Snale.',
        'url' => 'https://example.com/source',
    ]);
}

it('lists only the sauce answers the user had accepted', function () {
    $owner = User::factory()->create(['username' => 'op']);
    $member = User::factory()->create(['username' => 'helper']);

    // Accepted answer by $member.
    $acceptedRequest = makeSauceRequest($owner);
    $acceptedAnswer = makeAnswerOnSauceRequest($acceptedRequest, $member);
    $acceptedRequest->update(['accepted_sauce' => $acceptedAnswer->id]);

    // Non-accepted answer by $member (same request, but not accepted).
    makeAnswerOnSauceRequest($acceptedRequest, $member);

    $this->get(route('profile.accepted-answers', $member))
        ->assertOk()
        ->assertSee('1 Accepted sauce answer')
        ->assertSee($acceptedAnswer->content);
});

it('excludes answers that belong to other users', function () {
    $owner = User::factory()->create(['username' => 'op']);
    $alice = User::factory()->create(['username' => 'alice']);
    $bob = User::factory()->create(['username' => 'bob']);

    $request = makeSauceRequest($owner);
    $aliceAnswer = makeAnswerOnSauceRequest($request, $alice);
    makeAnswerOnSauceRequest($request, $bob);

    $request->update(['accepted_sauce' => $aliceAnswer->id]);

    $this->get(route('profile.accepted-answers', $bob))
        ->assertOk()
        ->assertSee('0 Accepted sauce answers')
        ->assertDontSee($aliceAnswer->content);
});

it('shows an empty state when the user has no accepted answers', function () {
    $member = User::factory()->create(['username' => 'helper']);

    $this->get(route('profile.accepted-answers', $member))
        ->assertOk()
        ->assertSee('has no accepted sauce answers yet.');
});

it('links each accepted answer to its parent sauce request', function () {
    $owner = User::factory()->create(['username' => 'op']);
    $member = User::factory()->create(['username' => 'helper']);

    $request = makeSauceRequest($owner, ['title' => 'Original title']);
    $acceptedAnswer = makeAnswerOnSauceRequest($request, $member);
    $request->update(['accepted_sauce' => $acceptedAnswer->id]);

    $this->get(route('profile.accepted-answers', $member))
        ->assertOk()
        ->assertSee(route('sauce-requests.show', $request));
});

it('allows guests to view the accepted answers list', function () {
    $owner = User::factory()->create(['username' => 'op']);
    $member = User::factory()->create(['username' => 'helper']);

    $request = makeSauceRequest($owner);
    $acceptedAnswer = makeAnswerOnSauceRequest($request, $member);
    $request->update(['accepted_sauce' => $acceptedAnswer->id]);

    $this->get(route('profile.accepted-answers', $member))
        ->assertOk();
});