<?php

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a sauce answer on the given sauce request.
 */
function makeAnswer(SauceRequest $sauceRequest, User $user, array $attributes = []): SauceAnswer
{
    return SauceAnswer::create(array_merge([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $user->id,
        'content' => 'Artist is Snale.',
        'url' => null,
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Posting answers
// ---------------------------------------------------------------------------

it('lets any authenticated user provide an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'Artist is Snale.',
            'url' => 'https://x.com/04119__snail/status/1414620876159418370',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $answer = SauceAnswer::firstOrFail();
    expect($answer->sauce_request_id)->toBe($sauceRequest->id);
    expect($answer->user_id)->toBe($member->id);
    expect($answer->content)->toBe('Artist is Snale.');
    expect($answer->url)->toBe('https://x.com/04119__snail/status/1414620876159418370');
});

it('allows an answer without a source url', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'This is a manga panel.',
        ])
        ->assertRedirect();

    $answer = SauceAnswer::firstOrFail();
    expect($answer->url)->toBeNull();
});

it('redirects guests away from the answer route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->post(route('sauce-requests.answers.store', $sauceRequest), [
        'content' => 'Hello',
    ])->assertRedirect(route('login'));
});

it('requires answer content', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => '',
        ])
        ->assertSessionHasErrors('content');

    expect(SauceAnswer::count())->toBe(0);
});

it('rejects an invalid source url', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'Artist is Snale.',
            'url' => 'not-a-url',
        ])
        ->assertSessionHasErrors('url');

    expect(SauceAnswer::count())->toBe(0);
});

it('rejects answers on unpublished drafts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, ['published_at' => null]);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'Hello',
        ])
        ->assertNotFound();

    expect(SauceAnswer::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Deleting answers
// ---------------------------------------------------------------------------

it('lets the author delete their own answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.destroy', [$sauceRequest, $answer]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($answer->fresh()->trashed())->toBeTrue();
});

it('lets staff delete any answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $this->actingAs($moderator)
        ->delete(route('sauce-requests.answers.destroy', [$sauceRequest, $answer]))
        ->assertRedirect();

    expect($answer->fresh()->trashed())->toBeTrue();
});

it('forbids a member from deleting someone elses answer', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $author);

    $this->actingAs($other)
        ->delete(route('sauce-requests.answers.destroy', [$sauceRequest, $answer]))
        ->assertForbidden();

    expect($answer->fresh()->trashed())->toBeFalse();
});

it('redirects guests away from the delete route', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $author);

    $this->delete(route('sauce-requests.answers.destroy', [$sauceRequest, $answer]))
        ->assertRedirect(route('login'));

    expect($answer->fresh()->trashed())->toBeFalse();
});

it('clears the accepted answer when the accepted answer is deleted', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $member);

    $sauceRequest->update(['accepted_sauce' => $answer->id]);

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.destroy', [$sauceRequest, $answer]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->accepted_sauce)->toBeNull();
});

it('rejects deleting an answer from a different sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $answer = makeAnswer($first, $member);

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.destroy', [$second, $answer]))
        ->assertStatus(422);

    expect($answer->fresh()->trashed())->toBeFalse();
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 5 answers per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => "answer {$i}",
        ])->assertRedirect();
    }

    $this->post(route('sauce-requests.answers.store', $sauceRequest), [
        'content' => 'answer 6',
    ])->assertTooManyRequests();
});