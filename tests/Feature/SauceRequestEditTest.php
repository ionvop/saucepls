<?php

use App\Models\SauceRequest;
use App\Models\User;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makeSauceRequest(User $user, array $attributes = []): SauceRequest
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
// Viewing the edit form
// ---------------------------------------------------------------------------

it('redirects guests away from the edit form', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->get(route('sauce-requests.edit', $sauceRequest))
        ->assertRedirect(route('login'));
});

it('redirects guests away from the update action', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->put(route('sauce-requests.update', $sauceRequest), [
        'title' => 'New title',
    ])->assertRedirect(route('login'));
});

it('shows the edit form to the owner with prefilled values', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'title' => 'My title',
        'description' => 'My description.',
    ]);

    $this->actingAs($owner)
        ->get(route('sauce-requests.edit', $sauceRequest))
        ->assertOk()
        ->assertSee('My title')
        ->assertSee('My description');
});

it('forbids a non-owner from viewing the edit form', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($other)
        ->get(route('sauce-requests.edit', $sauceRequest))
        ->assertForbidden();
});

it('forbids a non-owner from updating the request', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($other)
        ->put(route('sauce-requests.update', $sauceRequest), [
            'title' => 'Hacked title',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
        'title' => 'Original title',
    ]);
});

// ---------------------------------------------------------------------------
// Updating the request
// ---------------------------------------------------------------------------

it('updates the title, description, and explicit flag', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($owner)
        ->put(route('sauce-requests.update', $sauceRequest), [
            'title' => 'Updated title',
            'description' => 'Updated description.',
            'is_explicit' => '0',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
        'title' => 'Updated title',
        'description' => 'Updated description.',
        'is_explicit' => false,
    ]);
});

it('keeps the existing image and phash when updating', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'image_path' => 'sauce-requests/original.png',
        'phash64' => 'bbbbbbbbbbbbbbbb',
    ]);

    $this->actingAs($owner)
        ->put(route('sauce-requests.update', $sauceRequest), [
            'title' => 'New title',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
        'image_path' => 'sauce-requests/original.png',
        'phash64' => 'bbbbbbbbbbbbbbbb',
    ]);
});

it('allows the owner to edit a solved request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'accepted_sauce' => 1,
    ]);

    $this->actingAs($owner)
        ->put(route('sauce-requests.update', $sauceRequest), [
            'title' => 'Edited after solving',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
        'title' => 'Edited after solving',
    ]);
});

it('rejects a title that is too long', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($owner)
        ->put(route('sauce-requests.update', $sauceRequest), [
            'title' => str_repeat('a', 121),
        ])
        ->assertSessionHasErrors('title');
});

// ---------------------------------------------------------------------------
// Uploading an image (draft creation)
// ---------------------------------------------------------------------------

it('creates a draft sauce request when uploading an image', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'description' => 'Found it on Discord.',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect();

    $sauceRequest = SauceRequest::firstOrFail();

    expect($sauceRequest->status)->toBe(SauceRequest::STATUS_DRAFT);
    expect($sauceRequest->title)->toBe('Who drew this?');
    expect($sauceRequest->description)->toBe('Found it on Discord.');
    expect($sauceRequest->image_path)->not->toBeNull();
});

it('redirects guests away from the upload action', function () {
    $this->post(route('sauce-requests.upload'), [
        'image' => UploadedFile::fake()->image('art.png'),
    ])->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Viewing the details page
// ---------------------------------------------------------------------------

it('redirects guests away from the details page', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->get(route('sauce-requests.details', $sauceRequest))
        ->assertRedirect(route('login'));
});

it('forbids a non-owner from viewing the details page', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($other)
        ->get(route('sauce-requests.details', $sauceRequest))
        ->assertForbidden();
});

it('shows the details page to the owner with prefilled text and tags', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'text' => 'Hello world',
    ]);

    app(TagService::class)->add($sauceRequest, ['1girl', 'smile'], $owner);

    $this->actingAs($owner)
        ->get(route('sauce-requests.details', $sauceRequest))
        ->assertOk()
        ->assertSee('Hello world')
        ->assertSee('1girl smile');
});

// ---------------------------------------------------------------------------
// Publishing a draft
// ---------------------------------------------------------------------------

it('publishes a draft with the edited text and tags', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'text' => 'Original OCR text',
        'status' => SauceRequest::STATUS_DRAFT,
    ]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.publish', $sauceRequest), [
            'text' => 'Edited text',
            'tags' => '1girl black_hair',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    $fresh = $sauceRequest->fresh();

    expect($fresh->status)->toBe(SauceRequest::STATUS_POSTED);
    expect($fresh->text)->toBe('Edited text');
    expect($fresh->tags->pluck('name')->all())->toBe(['1girl', 'black_hair']);
});

it('redirects guests away from the publish action', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->post(route('sauce-requests.publish', $sauceRequest), [
        'text' => 'Edited text',
    ])->assertRedirect(route('login'));
});

it('forbids a non-owner from publishing a draft', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'status' => SauceRequest::STATUS_DRAFT,
    ]);

    $this->actingAs($other)
        ->post(route('sauce-requests.publish', $sauceRequest), [
            'text' => 'Hacked text',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
        'status' => SauceRequest::STATUS_DRAFT,
    ]);
});