<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestTaggingHistory;
use App\Models\Tag;
use App\Models\User;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makeTaggedSauceRequest(User $user, array $attributes = []): SauceRequest
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
// Tag normalization
// ---------------------------------------------------------------------------

it('normalizes tags to lowercase', function () {
    $service = app(TagService::class);

    expect($service->normalize('1Girl Black_Hair'))->toBe(['1girl', 'black_hair']);
});

it('strips characters that are not alphanumeric, hyphens, or underscores', function () {
    $service = app(TagService::class);

    expect($service->normalize('coconut! doggy?'))->toBe(['coconut', 'doggy']);
});

it('strips leading hyphens from tags', function () {
    $service = app(TagService::class);

    expect($service->normalize('-kitty -1girl'))->toBe(['kitty', '1girl']);
});

it('ignores duplicate tags', function () {
    $service = app(TagService::class);

    expect($service->normalize('smile smile smile'))->toBe(['smile']);
});

it('drops empty tags', function () {
    $service = app(TagService::class);

    expect($service->normalize('   '))->toBe([]);
});

// ---------------------------------------------------------------------------
// Storing tags with a new sauce request
// ---------------------------------------------------------------------------

it('persists user-entered tags when creating a sauce request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('sauce-requests.store'), [
            'title' => 'Who drew this?',
            'description' => 'Found it on Discord.',
            'image' => UploadedFile::fake()->image('art.png'),
            'tags' => '1girl Black_Hair smile',
        ])
        ->assertRedirect();

    $sauceRequest = SauceRequest::firstOrFail();

    expect($sauceRequest->tags->pluck('name')->all())
        ->toBe(['1girl', 'black_hair', 'smile']);
});

it('records tagging history when creating a sauce request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('sauce-requests.store'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
            'tags' => '1girl smile',
        ])
        ->assertRedirect();

    $sauceRequest = SauceRequest::firstOrFail();

    $this->assertDatabaseHas('sauce_request_tagging_history', [
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $user->id,
    ]);

    $history = SauceRequestTaggingHistory::firstOrFail();
    expect($history->added_tags)->toBe(['1girl', 'smile']);
    expect($history->removed_tags)->toBe([]);
});

// ---------------------------------------------------------------------------
// Updating tags on an existing sauce request
// ---------------------------------------------------------------------------

it('syncs tags when updating a sauce request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);

    $this->actingAs($owner)
        ->put(route('sauce-requests.update', $sauceRequest), [
            'title' => 'Updated title',
            'tags' => '1girl black_hair',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['1girl', 'black_hair']);
});

it('records added and removed tags in history when updating', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);

    $this->actingAs($owner)
        ->put(route('sauce-requests.update', $sauceRequest), [
            'title' => 'Updated title',
            'tags' => '1girl black_hair',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    $history = SauceRequestTaggingHistory::latest('id')->firstOrFail();
    expect($history->added_tags)->toBe(['black_hair']);
    expect($history->removed_tags)->toBe(['smile']);
});

// ---------------------------------------------------------------------------
// Community tagging
// ---------------------------------------------------------------------------

it('lets any authenticated user add tags', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.tags.store', $sauceRequest), [
            'tags' => 'cute cat_ears',
        ])
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['cute', 'cat_ears']);

    $history = SauceRequestTaggingHistory::firstOrFail();
    expect($history->user_id)->toBe($member->id);
    expect($history->added_tags)->toBe(['cute', 'cat_ears']);
});

it('lets any authenticated user remove a tag', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);
    $tag = Tag::where('name', 'smile')->firstOrFail();

    $this->actingAs($member)
        ->delete(route('sauce-requests.tags.destroy', [$sauceRequest, $tag]))
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['1girl']);

    $history = SauceRequestTaggingHistory::latest('id')->firstOrFail();
    expect($history->user_id)->toBe($member->id);
    expect($history->removed_tags)->toBe(['smile']);
});

it('redirects guests away from the tag routes', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    $this->post(route('sauce-requests.tags.store', $sauceRequest), ['tags' => 'cute'])
        ->assertRedirect(route('login'));

    $this->delete(route('sauce-requests.tags.destroy', [$sauceRequest, 1]))
        ->assertRedirect(route('login'));
});

it('does not duplicate tags when adding an existing tag', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl'], $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.tags.store', $sauceRequest), [
            'tags' => '1girl',
        ])
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['1girl']);

    // No new history row should be recorded for a no-op add.
    expect(SauceRequestTaggingHistory::count())->toBe(1);
});
