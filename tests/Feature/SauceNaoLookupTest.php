<?php

use App\Services\SauceNaoService;
use Illuminate\Support\Facades\Http;

/**
 * Build a fake SauceNAO API response payload.
 */
function fakeSauceNaoPayload(array $results = []): array
{
    return [
        'header' => [
            'status' => 0,
            'results_returned' => count($results),
        ],
        'results' => $results,
    ];
}

/**
 * Build a single fake SauceNAO result.
 */
function fakeSauceNaoResult(float $similarity, array $data = []): array
{
    return [
        'header' => [
            'similarity' => (string) $similarity,
            'thumbnail' => 'https://img1.saucenao.com/res/pixiv/1/1.jpg',
            'index_id' => 5,
            'index_name' => 'Index #5: Pixiv Images',
        ],
        'data' => array_merge([
            'ext_urls' => ['https://www.pixiv.net/member_illust.php?mode=medium&illust_id=1'],
            'title' => 'Example artwork',
            'member_name' => 'hiraken',
        ], $data),
    ];
}

// ---------------------------------------------------------------------------
// Service-level tests
// ---------------------------------------------------------------------------

it('returns normalized matches above the minimum similarity', function () {
    $path = tempnam(sys_get_temp_dir(), 'sauce');

    Http::fake([
        'saucenao.com/*' => Http::response(fakeSauceNaoPayload([
            fakeSauceNaoResult(92.77),
            fakeSauceNaoResult(32.81),
        ])),
    ]);

    $matches = app(SauceNaoService::class)->lookup($path);

    expect($matches)->toHaveCount(1)
        ->and($matches[0]['similarity'])->toBe(92.77)
        ->and($matches[0]['title'])->toBe('Example artwork')
        ->and($matches[0]['author'])->toBe('hiraken')
        ->and($matches[0]['urls'])->toBe(['https://www.pixiv.net/member_illust.php?mode=medium&illust_id=1']);
});

it('filters out results below the minimum similarity', function () {
    $path = tempnam(sys_get_temp_dir(), 'sauce');

    Http::fake([
        'saucenao.com/*' => Http::response(fakeSauceNaoPayload([
            fakeSauceNaoResult(40.0),
            fakeSauceNaoResult(55.0),
        ])),
    ]);

    $matches = app(SauceNaoService::class)->lookup($path);

    expect($matches)->toBe([]);
});

it('returns an empty array when the API reports an error status', function () {
    $path = tempnam(sys_get_temp_dir(), 'sauce');

    Http::fake([
        'saucenao.com/*' => Http::response([
            'header' => ['status' => 4],
            'results' => [],
        ]),
    ]);

    $matches = app(SauceNaoService::class)->lookup($path);

    expect($matches)->toBe([]);
});

it('returns an empty array when the API request fails', function () {
    $path = tempnam(sys_get_temp_dir(), 'sauce');

    Http::fake([
        'saucenao.com/*' => Http::response('Server error', 500),
    ]);

    $matches = app(SauceNaoService::class)->lookup($path);

    expect($matches)->toBe([]);
});

it('sends the image as a multipart file upload', function () {
    $path = tempnam(sys_get_temp_dir(), 'sauce');

    Http::fake();

    app(SauceNaoService::class)->lookup($path);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://saucenao.com/search.php'
            && $request->hasFile('file');
    });
});