<?php

use App\Models\SauceRequest;
use App\Models\User;
use App\Services\SauceNaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makeSauceRequestForSauce(User $user, array $attributes = []): SauceRequest
{
    return SauceRequest::create(array_merge([
        'user_id' => $user->id,
        'title' => 'Who drew this?',
        'description' => '',
        'text' => '',
        'image_path' => 'sauce-requests/test.png',
        'phash64' => 'aaaaaaaaaaaaaaaa',
        'is_explicit' => true,
        'published_at' => now(),
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Upload redirects to the SauceNAO page when a match is found
// ---------------------------------------------------------------------------

it('redirects to the sauce page when SauceNAO finds a match', function () {
    $user = User::factory()->create();

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([
            [
                'similarity' => 92.77,
                'thumbnail' => 'https://img1.saucenao.com/res/pixiv/1/1.jpg',
                'index_id' => 5,
                'index_name' => 'Index #5: Pixiv Images',
                'urls' => ['https://www.pixiv.net/member_illust.php?mode=medium&illust_id=1'],
                'title' => 'Example artwork',
                'author' => 'hiraken',
            ],
        ]);

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect(route('sauce-requests.sauce', SauceRequest::firstOrFail()));
});

it('redirects to the details page when SauceNAO finds no match', function () {
    $user = User::factory()->create();

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
// Viewing the sauce page
// ---------------------------------------------------------------------------

it('shows the sauce page to the owner with the cached match', function () {
    $user = User::factory()->create();
    $sauceRequest = makeSauceRequestForSauce($user);

    Cache::put(
        "sauce-requests.{$sauceRequest->id}.saucenao",
        [
            [
                'similarity' => 92.77,
                'thumbnail' => 'https://img1.saucenao.com/res/pixiv/1/1.jpg',
                'index_id' => 5,
                'index_name' => 'Index #5: Pixiv Images',
                'urls' => ['https://www.pixiv.net/member_illust.php?mode=medium&illust_id=1'],
                'title' => 'Example artwork',
                'author' => 'hiraken',
            ],
        ],
        now()->addMinutes(10),
    );

    $this->actingAs($user)
        ->get(route('sauce-requests.sauce', $sauceRequest))
        ->assertOk()
        ->assertSee('We found a match for your image')
        ->assertSee('Example artwork')
        ->assertSee('92.8% match')
        ->assertSee('Continue anyway');
});

it('forbids a non-owner from viewing the sauce page', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequestForSauce($owner);

    $this->actingAs($other)
        ->get(route('sauce-requests.sauce', $sauceRequest))
        ->assertForbidden();
});

it('redirects guests away from the sauce page', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequestForSauce($owner);

    $this->get(route('sauce-requests.sauce', $sauceRequest))
        ->assertRedirect(route('login'));
});