<?php

use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Accepting answers
// ---------------------------------------------------------------------------

it('lets the author accept an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $this->actingAs($owner)
        ->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($sauceRequest->fresh()->accepted_sauce)->toBe($answer->id);
});

it('lets staff accept an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $this->actingAs($moderator)
        ->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->accepted_sauce)->toBe($answer->id);
});

it('forbids a member from accepting an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $this->actingAs($other)
        ->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertForbidden();

    expect($sauceRequest->fresh()->accepted_sauce)->toBeNull();
});

it('redirects guests away from the accept route', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $this->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertRedirect(route('login'));

    expect($sauceRequest->fresh()->accepted_sauce)->toBeNull();
});

it('rejects accepting an answer from a different sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $answer = makeAnswer($first, $member);

    $this->actingAs($owner)
        ->post(route('sauce-requests.answers.accept', [$second, $answer]))
        ->assertStatus(422);

    expect($first->fresh()->accepted_sauce)->toBeNull();
});

// ---------------------------------------------------------------------------
// Un-accepting answers
// ---------------------------------------------------------------------------

it('lets the author un-accept an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $sauceRequest->update(['accepted_sauce' => $answer->id]);

    $this->actingAs($owner)
        ->delete(route('sauce-requests.answers.unaccept', [$sauceRequest, $answer]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($sauceRequest->fresh()->accepted_sauce)->toBeNull();
});

it('lets staff un-accept an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $sauceRequest->update(['accepted_sauce' => $answer->id]);

    $this->actingAs($moderator)
        ->delete(route('sauce-requests.answers.unaccept', [$sauceRequest, $answer]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->accepted_sauce)->toBeNull();
});

it('forbids a member from un-accepting an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $sauceRequest->update(['accepted_sauce' => $answer->id]);

    $this->actingAs($other)
        ->delete(route('sauce-requests.answers.unaccept', [$sauceRequest, $answer]))
        ->assertForbidden();

    expect($sauceRequest->fresh()->accepted_sauce)->toBe($answer->id);
});

it('un-accepting a non-accepted answer is a no-op', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $accepted = makeAnswer($sauceRequest, $member);
    $other = makeAnswer($sauceRequest, $member);

    $sauceRequest->update(['accepted_sauce' => $accepted->id]);

    $this->actingAs($owner)
        ->delete(route('sauce-requests.answers.unaccept', [$sauceRequest, $other]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->accepted_sauce)->toBe($accepted->id);
});