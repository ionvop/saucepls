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

// ---------------------------------------------------------------------------
// Reverting a single change
// ---------------------------------------------------------------------------

it('redirects guests away from the revert route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeHistorySauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl'], $owner);
    $history = SauceRequestTaggingHistory::firstOrFail();

    $this->post(route('sauce-requests.tags.history.revert', [$sauceRequest, $history]))
        ->assertRedirect(route('login'));
});

it('reverts a single tagging change and records a compensating entry', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeHistorySauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);
    app(TagService::class)->add($sauceRequest, ['cat_ears'], $member);

    $change = SauceRequestTaggingHistory::whereJsonContains('added_tags', 'cat_ears')->firstOrFail();

    $this->actingAs($member)
        ->post(route('sauce-requests.tags.history.revert', [$sauceRequest, $change]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['1girl', 'smile']);

    $compensating = SauceRequestTaggingHistory::latest('id')->firstOrFail();
    expect($compensating->user_id)->toBe($member->id);
    expect($compensating->removed_tags)->toBe(['cat_ears']);
    expect($compensating->added_tags)->toBe([]);
});

it('does not record a compensating entry when reverting a no-op change', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeHistorySauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl'], $owner);
    app(TagService::class)->remove($sauceRequest, ['1girl'], $member);

    // The first change added '1girl', which has since been removed, so
    // reverting it is a no-op and should not record anything.
    $history = SauceRequestTaggingHistory::whereJsonContains('added_tags', '1girl')->firstOrFail();

    $this->actingAs($member)
        ->post(route('sauce-requests.tags.history.revert', [$sauceRequest, $history]))
        ->assertRedirect();

    expect(SauceRequestTaggingHistory::count())->toBe(2);
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
        ->post(route('sauce-requests.tags.history.revert', [$sauceRequestB, $history]))
        ->assertNotFound();
});