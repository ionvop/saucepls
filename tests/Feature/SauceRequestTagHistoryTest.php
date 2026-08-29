<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestTaggingHistory;
use App\Models\User;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makeHistorySauceRequest(User $user, array $attributes = []): SauceRequest
{
    return SauceRequest::create(array_merge([
        'user_id' => $user->id,
        'title' => 'Original title',
        'description' => 'Original description.',
        'text' => '',
        'image_path' => 'sauce-requests/test.png',
        'phash64' => 'aaaaaaaaaaaaaaaa',
        'is_explicit' => true,
        'published_at' => now(),
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Viewing history
// ---------------------------------------------------------------------------

it('redirects guests away from the history page', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeHistorySauceRequest($owner);

    $this->get(route('sauce-requests.tags.history', $sauceRequest))
        ->assertRedirect(route('login'));
});

it('lets any authenticated user view the tagging history', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeHistorySauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);
    app(TagService::class)->add($sauceRequest, ['cat_ears'], $member);

    $this->actingAs($member)
        ->get(route('sauce-requests.tags.history', $sauceRequest))
        ->assertOk()
        ->assertSee('Tagging history')
        ->assertSee('1girl')
        ->assertSee('cat_ears');
});

it('stores a snapshot of the resulting tags on each history entry', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeHistorySauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);
    app(TagService::class)->add($sauceRequest, ['cat_ears'], $member);
    app(TagService::class)->remove($sauceRequest, ['smile'], $member);

    $history = SauceRequestTaggingHistory::orderBy('id')->get();

    expect($history[0]->tags_snapshot)->toBe(['1girl', 'smile']);
    expect($history[1]->tags_snapshot)->toBe(['1girl', 'smile', 'cat_ears']);
    expect($history[2]->tags_snapshot)->toBe(['1girl', 'cat_ears']);
});

// ---------------------------------------------------------------------------
// Restoring to a past state
// ---------------------------------------------------------------------------

it('restores tags to the state after a given history entry', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeHistorySauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);
    app(TagService::class)->add($sauceRequest, ['cat_ears'], $member);
    app(TagService::class)->remove($sauceRequest, ['smile'], $member);

    // Target: the state right after the second change (1girl, smile, cat_ears).
    $target = SauceRequestTaggingHistory::whereJsonContains('added_tags', 'cat_ears')->firstOrFail();

    $this->actingAs($member)
        ->post(route('sauce-requests.tags.history.restore', [$sauceRequest, $target]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['1girl', 'smile', 'cat_ears']);

    // The restore itself is recorded as a compensating entry.
    $compensating = SauceRequestTaggingHistory::latest('id')->firstOrFail();
    expect($compensating->added_tags)->toBe(['smile']);
    expect($compensating->tags_snapshot)->toBe(['1girl', 'smile', 'cat_ears']);
});

// ---------------------------------------------------------------------------
// Scoping
// ---------------------------------------------------------------------------

it('returns 404 when the history entry belongs to another sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequestA = makeHistorySauceRequest($owner);
    $sauceRequestB = makeHistorySauceRequest($owner);

    app(TagService::class)->add($sauceRequestA, ['1girl'], $owner);
    $history = SauceRequestTaggingHistory::firstOrFail();

    $this->actingAs($member)
        ->post(route('sauce-requests.tags.history.restore', [$sauceRequestB, $history]))
        ->assertNotFound();
});