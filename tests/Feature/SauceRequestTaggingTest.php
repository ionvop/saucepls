<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestTaggingHistory;
use App\Models\Tag;
use App\Models\User;
use App\Services\OcrService;
use App\Services\SauceNaoService;
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
        'published_at' => now(),
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

it('persists user-entered tags when publishing a sauce request', function () {
    $user = User::factory()->create();

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->once()
        ->andReturn('');

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'description' => 'Found it on Discord.',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect();

    $sauceRequest = SauceRequest::firstOrFail();

    $this->actingAs($user)
        ->post(route('sauce-requests.publish', $sauceRequest), [
            'tags' => '1girl Black_Hair smile',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['1girl', 'black_hair', 'smile']);
});

it('records tagging history when publishing a sauce request', function () {
    $user = User::factory()->create();

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->once()
        ->andReturn('');

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect();

    $sauceRequest = SauceRequest::firstOrFail();

    $this->actingAs($user)
        ->post(route('sauce-requests.publish', $sauceRequest), [
            'tags' => '1girl smile',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    $this->assertDatabaseHas('sauce_request_tagging_history', [
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $user->id,
    ]);

    $history = SauceRequestTaggingHistory::firstOrFail();
    expect($history->added_tags)->toBe(['1girl', 'smile']);
    expect($history->removed_tags)->toBe([]);
    expect($history->tags_snapshot)->toBe(['1girl', 'smile']);
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

it('lets any authenticated user replace the full tag set', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);

    $this->actingAs($member)
        ->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => 'cute cat_ears',
        ])
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['cute', 'cat_ears']);

    $history = SauceRequestTaggingHistory::latest('id')->firstOrFail();
    expect($history->user_id)->toBe($member->id);
    expect($history->added_tags)->toBe(['cute', 'cat_ears']);
    expect($history->removed_tags)->toBe(['1girl', 'smile']);
});

it('lets any authenticated user remove all tags by clearing the field', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);

    $this->actingAs($member)
        ->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => '',
        ])
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe([]);

    $history = SauceRequestTaggingHistory::latest('id')->firstOrFail();
    expect($history->user_id)->toBe($member->id);
    expect($history->removed_tags)->toBe(['1girl', 'smile']);
});

it('redirects guests away from the tag route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    $this->put(route('sauce-requests.tags.update', $sauceRequest), ['tags' => 'cute'])
        ->assertRedirect(route('login'));
});

it('does not duplicate tags when syncing an unchanged set', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl'], $owner);

    $this->actingAs($member)
        ->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => '1girl',
        ])
        ->assertRedirect();

    expect($sauceRequest->fresh()->tags->pluck('name')->all())
        ->toBe(['1girl']);

    // No new history row should be recorded for a no-op sync.
    expect(SauceRequestTaggingHistory::count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Rate limiting community tag modifications
// ---------------------------------------------------------------------------

it('rate limits a member to 5 tag modifications per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    $this->actingAs($member);

    for ($i = 0; $i < 5; $i++) {
        $this->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => "tag{$i}",
        ])->assertRedirect();
    }

    $this->put(route('sauce-requests.tags.update', $sauceRequest), [
        'tags' => 'tag6',
    ])->assertTooManyRequests();
});

it('does not rate limit the owner of the sauce request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    $this->actingAs($owner);

    for ($i = 0; $i < 10; $i++) {
        $this->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => "tag{$i}",
        ])->assertRedirect();
    }
});

it('does not rate limit moderators', function () {
    $owner = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeTaggedSauceRequest($owner);

    $this->actingAs($moderator);

    for ($i = 0; $i < 10; $i++) {
        $this->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => "tag{$i}",
        ])->assertRedirect();
    }
});

it('does not rate limit admins', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['type' => 'admin']);
    $sauceRequest = makeTaggedSauceRequest($owner);

    $this->actingAs($admin);

    for ($i = 0; $i < 10; $i++) {
        $this->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => "tag{$i}",
        ])->assertRedirect();
    }
});

it('rate limits the restore route for members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTaggedSauceRequest($owner);

    app(TagService::class)->add($sauceRequest, ['1girl'], $owner);
    $target = SauceRequestTaggingHistory::firstOrFail();

    $this->actingAs($member);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('sauce-requests.tags.history.restore', [$sauceRequest, $target]))
            ->assertRedirect();
    }

    $this->post(route('sauce-requests.tags.history.restore', [$sauceRequest, $target]))
        ->assertTooManyRequests();
});
