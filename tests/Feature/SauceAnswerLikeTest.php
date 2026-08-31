<?php

use App\Models\SauceAnswerLike;
use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Liking answers
// ---------------------------------------------------------------------------

it('lets any authenticated user like an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.like', [$sauceRequest, $answer]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceAnswerLike::count())->toBe(1);
    $like = SauceAnswerLike::firstOrFail();
    expect($like->sauce_answer_id)->toBe($answer->id);
    expect($like->user_id)->toBe($member->id);
});

it('does not create duplicate likes for the same user', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.like', [$sauceRequest, $answer]))
        ->assertRedirect();

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.like', [$sauceRequest, $answer]))
        ->assertRedirect();

    expect(SauceAnswerLike::count())->toBe(1);
});

it('redirects guests away from the like route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->post(route('sauce-requests.answers.like', [$sauceRequest, $answer]))
        ->assertRedirect(route('login'));

    expect(SauceAnswerLike::count())->toBe(0);
});

it('rejects likes on unpublished drafts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, ['published_at' => null]);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.like', [$sauceRequest, $answer]))
        ->assertNotFound();

    expect(SauceAnswerLike::count())->toBe(0);
});

it('rejects a like on an answer from a different sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $answer = makeAnswer($first, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.like', [$second, $answer]))
        ->assertStatus(422);

    expect(SauceAnswerLike::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Unliking answers
// ---------------------------------------------------------------------------

it('lets a user unlike an answer they liked', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.like', [$sauceRequest, $answer]))
        ->assertRedirect();

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.unlike', [$sauceRequest, $answer]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceAnswerLike::count())->toBe(0);
});

it('unliking an answer that was never liked is a no-op', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.unlike', [$sauceRequest, $answer]))
        ->assertRedirect();

    expect(SauceAnswerLike::count())->toBe(0);
});

it('redirects guests away from the unlike route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->delete(route('sauce-requests.answers.unlike', [$sauceRequest, $answer]))
        ->assertRedirect(route('login'));

    expect(SauceAnswerLike::count())->toBe(0);
});