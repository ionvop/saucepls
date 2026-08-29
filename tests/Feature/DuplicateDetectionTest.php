<?php

use App\Models\SauceRequest;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\PerceptualHashService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makePhashSauceRequest(User $user, string $phash): SauceRequest
{
    return SauceRequest::create([
        'user_id' => $user->id,
        'title' => 'Original title',
        'description' => 'Original description.',
        'text' => '',
        'image_path' => 'sauce-requests/test.png',
        'phash64' => $phash,
        'is_explicit' => true,
    ]);
}

// ---------------------------------------------------------------------------
// PerceptualHashService::distance
// ---------------------------------------------------------------------------

it('computes the hamming distance between two hashes', function () {
    $service = app(PerceptualHashService::class);

    // 0x00 vs 0x0f -> 4 differing bits.
    expect($service->distance('0000000000000000', '0f00000000000000'))->toBe(4);
    expect($service->distance('0000000000000000', '0000000000000000'))->toBe(0);
});

// ---------------------------------------------------------------------------
// DuplicateDetectionService::findDuplicate
// ---------------------------------------------------------------------------

it('returns the closest request within the threshold', function () {
    $user = User::factory()->create();
    $existing = makePhashSauceRequest($user, '0000000000000000');

    $service = app(DuplicateDetectionService::class);

    // 0x0f differs by 4 bits, well within the default threshold of 10.
    $match = $service->findDuplicate('0f00000000000000');

    expect($match?->is($existing))->toBeTrue();
});

it('returns null when no request is within the threshold', function () {
    $user = User::factory()->create();
    makePhashSauceRequest($user, '0000000000000000');

    $service = app(DuplicateDetectionService::class);

    // 0xff differs by 8 bits, still within the default threshold of 10.
    // Use a hash that differs by more than 10 bits instead.
    $match = $service->findDuplicate('ffffffffffffffff');

    expect($match)->toBeNull();
});

it('ignores soft-deleted requests when detecting duplicates', function () {
    $user = User::factory()->create();
    $existing = makePhashSauceRequest($user, '0000000000000000');
    $existing->delete();

    $service = app(DuplicateDetectionService::class);

    expect($service->findDuplicate('0f00000000000000'))->toBeNull();
});

it('does not flag a request as a duplicate of itself', function () {
    $user = User::factory()->create();
    $draft = makePhashSauceRequest($user, '0000000000000000');

    $service = app(DuplicateDetectionService::class);

    // The draft's own phash is an exact match (distance 0), but it must
    // be excluded so the upload is not flagged as a duplicate of itself.
    expect($service->findDuplicate('0000000000000000', $draft->id))->toBeNull();
});
