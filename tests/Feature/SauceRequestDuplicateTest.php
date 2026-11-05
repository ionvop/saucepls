<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestTaggingHistory;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\OcrService;
use App\Services\SauceNaoService;
use App\Services\TagInferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makeDuplicateSauceRequest(User $user, array $attributes = []): SauceRequest
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
        'enriched_at' => now(),
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Upload redirects to the duplicate page when a match is found
// ---------------------------------------------------------------------------

it('redirects to the duplicate page when a near-duplicate is found', function () {
    $user = User::factory()->create();
    $existing = makeDuplicateSauceRequest($user);

    $this->mock(OcrService::class)->shouldNotReceive('extractText');
    $this->mock(TagInferenceService::class)->shouldNotReceive('inferWithRating');
    $this->mock(SauceNaoService::class)->shouldNotReceive('lookup');

    $this->mock(DuplicateDetectionService::class)
        ->shouldReceive('findDuplicate')
        ->once()
        ->andReturn($existing);

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect(route('sauce-requests.duplicate', [
            SauceRequest::where('id', '!=', $existing->id)->firstOrFail(),
            'duplicate' => $existing,
        ]));
});

it('runs SauceNAO and then enriches after continuing past a duplicate', function () {
    $user = User::factory()->create();
    $existing = makeDuplicateSauceRequest($user);

    $this->mock(DuplicateDetectionService::class)
        ->shouldReceive('findDuplicate')
        ->once()
        ->andReturn($existing);

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->once()
        ->andReturn('Detected words');

    $this->mock(TagInferenceService::class)
        ->shouldReceive('inferWithRating')
        ->once()
        ->andReturn([
            'tags' => ['inferred_tag'],
            'rating' => null,
            'rating_confidence' => null,
        ]);

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect();

    $draft = SauceRequest::whereNull('published_at')->firstOrFail();

    $this->post(route('sauce-requests.continue-duplicate', $draft))
        ->assertRedirect(route('sauce-requests.details', $draft));

    expect($draft->fresh()->enriched_at)->not->toBeNull()
        ->and($draft->fresh()->text)->toBe('Detected words')
        ->and($draft->fresh()->tags->pluck('name')->all())->toBe(['inferred_tag'])
        ->and(SauceRequestTaggingHistory::where('sauce_request_id', $draft->id)->count())->toBe(0);

    $this->post(route('sauce-requests.continue-duplicate', $draft))
        ->assertRedirect(route('sauce-requests.details', $draft));
});

it('defers enrichment through both prompts when a duplicate has a SauceNAO match', function () {
    $user = User::factory()->create();
    $existing = makeDuplicateSauceRequest($user);

    $this->mock(DuplicateDetectionService::class)
        ->shouldReceive('findDuplicate')
        ->once()
        ->andReturn($existing);

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([['similarity' => 92.77]]);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->once()
        ->andReturn('Detected words');

    $this->mock(TagInferenceService::class)
        ->shouldReceive('inferWithRating')
        ->once()
        ->andReturn(emptyInferenceResult());

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect();

    $draft = SauceRequest::whereNull('published_at')->firstOrFail();

    $this->post(route('sauce-requests.continue-duplicate', $draft))
        ->assertRedirect(route('sauce-requests.sauce', $draft));

    expect($draft->fresh()->enriched_at)->toBeNull();

    $this->post(route('sauce-requests.continue-sauce', $draft))
        ->assertRedirect(route('sauce-requests.details', $draft));

    expect($draft->fresh()->enriched_at)->not->toBeNull()
        ->and($draft->fresh()->text)->toBe('Detected words');
});

it('redirects to the details page when no duplicate is found', function () {
    $user = User::factory()->create();

    $this->mock(DuplicateDetectionService::class)
        ->shouldReceive('findDuplicate')
        ->once()
        ->andReturn(null);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->once()
        ->andReturn('');

    $this->mock(TagInferenceService::class)
        ->shouldReceive('inferWithRating')
        ->once()
        ->andReturn(emptyInferenceResult());

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect(route('sauce-requests.details', SauceRequest::firstOrFail()));
});

// ---------------------------------------------------------------------------
// Viewing the duplicate page
// ---------------------------------------------------------------------------

it('shows the duplicate page to the owner', function () {
    $user = User::factory()->create();
    $sauceRequest = makeDuplicateSauceRequest($user);
    $existing = makeDuplicateSauceRequest($user, [
        'title' => 'Existing request',
    ]);

    $this->actingAs($user)
        ->get(route('sauce-requests.duplicate', [$sauceRequest, 'duplicate' => $existing]))
        ->assertOk()
        ->assertSee('This image may already have a request')
        ->assertSee('Existing request')
        ->assertSee('Continue anyway');
});

it('forbids a non-owner from viewing the duplicate page', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeDuplicateSauceRequest($owner);
    $existing = makeDuplicateSauceRequest($owner);

    $this->actingAs($other)
        ->get(route('sauce-requests.duplicate', [$sauceRequest, 'duplicate' => $existing]))
        ->assertForbidden();
});

it('forbids a non-owner from continuing either processing step', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeDuplicateSauceRequest($owner, ['enriched_at' => null]);

    $this->mock(SauceNaoService::class)->shouldNotReceive('lookup');
    $this->mock(OcrService::class)->shouldNotReceive('extractText');
    $this->mock(TagInferenceService::class)->shouldNotReceive('inferWithRating');

    $this->actingAs($other)
        ->post(route('sauce-requests.continue-duplicate', $sauceRequest))
        ->assertForbidden();

    $this->post(route('sauce-requests.continue-sauce', $sauceRequest))
        ->assertForbidden();
});

it('redirects guests away from the duplicate page', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeDuplicateSauceRequest($owner);
    $existing = makeDuplicateSauceRequest($owner);

    $this->get(route('sauce-requests.duplicate', [$sauceRequest, 'duplicate' => $existing]))
        ->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Rate limiting on upload
// ---------------------------------------------------------------------------

it('rate limits sauce request uploads', function () {
    $user = User::factory()->create();

    $this->mock(DuplicateDetectionService::class)
        ->shouldReceive('findDuplicate')
        ->andReturn(null);

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->andReturn([]);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->andReturn('');

    $this->mock(TagInferenceService::class)
        ->shouldReceive('inferWithRating')
        ->andReturn(emptyInferenceResult());

    $this->actingAs($user);

    for ($i = 0; $i < 3; $i++) {
        $this->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])->assertRedirect();
    }

    $this->post(route('sauce-requests.upload'), [
        'title' => 'Who drew this?',
        'image' => UploadedFile::fake()->image('art.png'),
    ])->assertTooManyRequests();
});
