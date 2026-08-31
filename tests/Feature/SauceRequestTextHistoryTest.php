<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestTextHistory;
use App\Models\User;
use App\Services\TextService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makeTextHistorySauceRequest(User $user, array $attributes = []): SauceRequest
{
    return SauceRequest::create(array_merge([
        'user_id' => $user->id,
        'title' => 'Original title',
        'description' => 'Original description.',
        'text' => 'capybara ?! capybara !',
        'image_path' => 'sauce-requests/test.png',
        'phash64' => 'aaaaaaaaaaaaaaaa',
        'is_explicit' => true,
        'published_at' => now(),
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Viewing history
// ---------------------------------------------------------------------------

it('redirects guests away from the text history page', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTextHistorySauceRequest($owner);

    $this->get(route('sauce-requests.text.history', $sauceRequest))
        ->assertRedirect(route('login'));
});

it('lets any authenticated user view the text history', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextHistorySauceRequest($owner);

    app(TextService::class)->update($sauceRequest, 'coconut doggy', $owner);
    app(TextService::class)->update($sauceRequest, 'o my gosh', $member);

    $this->actingAs($member)
        ->get(route('sauce-requests.text.history', $sauceRequest))
        ->assertOk()
        ->assertSee('Text history')
        ->assertSee('coconut doggy')
        ->assertSee('o my gosh');
});

it('stores a snapshot of the resulting text on each history entry', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextHistorySauceRequest($owner);

    app(TextService::class)->update($sauceRequest, 'coconut doggy', $owner);
    app(TextService::class)->update($sauceRequest, 'o my gosh', $member);
    app(TextService::class)->update($sauceRequest, '', $member);

    $history = SauceRequestTextHistory::orderBy('id')->get();

    expect($history[0]->text_snapshot)->toBe('coconut doggy');
    expect($history[1]->text_snapshot)->toBe('o my gosh');
    expect($history[2]->text_snapshot)->toBe('');
});

// ---------------------------------------------------------------------------
// Restoring to a past state
// ---------------------------------------------------------------------------

it('restores the text to the state after a given history entry', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextHistorySauceRequest($owner);

    app(TextService::class)->update($sauceRequest, 'coconut doggy', $owner);
    app(TextService::class)->update($sauceRequest, 'o my gosh', $member);

    // Target: the state right after the first change (coconut doggy).
    $target = SauceRequestTextHistory::orderBy('id')->firstOrFail();

    $this->actingAs($member)
        ->post(route('sauce-requests.text.history.restore', [$sauceRequest, $target]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->text)->toBe('coconut doggy');

    // The restore itself is recorded as a compensating entry.
    $compensating = SauceRequestTextHistory::latest('id')->firstOrFail();
    expect($compensating->text_snapshot)->toBe('coconut doggy');
});

// ---------------------------------------------------------------------------
// Scoping
// ---------------------------------------------------------------------------

it('returns 404 when the history entry belongs to another sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequestA = makeTextHistorySauceRequest($owner);
    $sauceRequestB = makeTextHistorySauceRequest($owner);

    app(TextService::class)->update($sauceRequestA, 'coconut doggy', $owner);
    $history = SauceRequestTextHistory::firstOrFail();

    $this->actingAs($member)
        ->post(route('sauce-requests.text.history.restore', [$sauceRequestB, $history]))
        ->assertNotFound();
});