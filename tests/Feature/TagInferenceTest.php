<?php

use App\Models\SauceRequest;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\SauceNaoService;
use App\Services\TagInferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Build a fake DeepDanbooru-style inference response payload.
 */
function fakeInferencePayload(array $items = []): array
{
    return $items;
}

/**
 * Build a single fake inference result.
 */
function fakeInferenceItem(string $tag, float $score): array
{
    return [
        'tag' => $tag,
        'score' => $score,
    ];
}

// ---------------------------------------------------------------------------
// Service-level tests
// ---------------------------------------------------------------------------

it('returns tags above the threshold', function () {
    $path = tempnam(sys_get_temp_dir(), 'infer');

    Http::fake([
        'deepdanbooru.nsk.sh/*' => Http::response(fakeInferencePayload([
            fakeInferenceItem('1girl', 0.99),
            fakeInferenceItem('smile', 0.97),
            fakeInferenceItem('blur', 0.05),
        ])),
    ]);

    $tags = app(TagInferenceService::class)->infer($path);

    expect($tags)->toBe(['1girl', 'smile']);
});

it('filters out rating tags', function () {
    $path = tempnam(sys_get_temp_dir(), 'infer');

    Http::fake([
        'deepdanbooru.nsk.sh/*' => Http::response(fakeInferencePayload([
            fakeInferenceItem('rating:safe', 0.99),
            fakeInferenceItem('1girl', 0.98),
            fakeInferenceItem('rating:explicit', 0.97),
        ])),
    ]);

    $tags = app(TagInferenceService::class)->infer($path);

    expect($tags)->toBe(['1girl']);
});

it('returns an empty array when no tags are above the threshold', function () {
    $path = tempnam(sys_get_temp_dir(), 'infer');

    Http::fake([
        'deepdanbooru.nsk.sh/*' => Http::response(fakeInferencePayload([
            fakeInferenceItem('blur', 0.05),
            fakeInferenceItem('lowres', 0.01),
        ])),
    ]);

    $tags = app(TagInferenceService::class)->infer($path);

    expect($tags)->toBe([]);
});

it('returns an empty array when the API request fails', function () {
    $path = tempnam(sys_get_temp_dir(), 'infer');

    Http::fake([
        'deepdanbooru.nsk.sh/*' => Http::response('Server error', 500),
    ]);

    $tags = app(TagInferenceService::class)->infer($path);

    expect($tags)->toBe([]);
});

it('sends the image as a multipart file upload with the threshold query param', function () {
    $path = tempnam(sys_get_temp_dir(), 'infer');

    Http::fake();

    app(TagInferenceService::class)->infer($path);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://deepdanbooru.nsk.sh/deepdanbooru?threshold=0.5'
            && $request->hasFile('image');
    });
});

// ---------------------------------------------------------------------------
// Feature-level tests
// ---------------------------------------------------------------------------

it('stores the inferred tags as the initial value of the tags field', function () {
    $user = User::factory()->create();

    $this->mock(DuplicateDetectionService::class)
        ->shouldReceive('findDuplicate')
        ->once()
        ->andReturn(null);

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    Http::fake([
        'deepdanbooru.nsk.sh/*' => Http::response(fakeInferencePayload([
            fakeInferenceItem('1girl', 0.99),
            fakeInferenceItem('black_hair', 0.97),
            fakeInferenceItem('rating:safe', 0.99),
            fakeInferenceItem('blur', 0.05),
        ])),
        'api.ocr.space/*' => Http::response([
            'ParsedResults' => [['ParsedText' => '']],
            'OCRExitCode' => 1,
            'IsErroredOnProcessing' => false,
        ]),
    ]);

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect(route('sauce-requests.details', SauceRequest::firstOrFail()));

    $sauceRequest = SauceRequest::firstOrFail();

    expect($sauceRequest->tags->pluck('name')->all())->toBe(['1girl', 'black_hair']);

    $this->get(route('sauce-requests.details', $sauceRequest))
        ->assertOk()
        ->assertSee('1girl black_hair');
});