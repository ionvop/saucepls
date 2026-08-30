<?php

use App\Models\SauceRequest;
use App\Models\User;
use App\Services\OcrService;
use App\Services\SauceNaoService;
use App\Services\TagInferenceService;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

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

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->once()
        ->andReturn('');

    $this->mock(TagInferenceService::class)
        ->shouldReceive('infer')
        ->once()
        ->andReturn([]);

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'description' => 'Found it on Discord.',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect();

    $sauceRequest = SauceRequest::firstOrFail();

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
    ]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.publish', $sauceRequest), [
            'text' => 'Edited text',
            'tags' => '1girl black_hair',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    $fresh = $sauceRequest->fresh();

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
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($other)
        ->post(route('sauce-requests.publish', $sauceRequest), [
            'text' => 'Hacked text',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
    ]);
});

// ---------------------------------------------------------------------------
// Publishing sets the published_at timestamp
// ---------------------------------------------------------------------------

it('sets published_at when a draft is published', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'published_at' => null,
    ]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.publish', $sauceRequest), [
            'text' => 'Edited text',
        ])
        ->assertRedirect(route('sauce-requests.show', $sauceRequest));

    expect($sauceRequest->fresh()->published_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Canceling a draft
// ---------------------------------------------------------------------------

it('cancels a draft and redirects to the upload form', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'published_at' => null,
    ]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.cancel', $sauceRequest))
        ->assertRedirect(route('create'));

    $this->assertSoftDeleted('sauce_requests', [
        'id' => $sauceRequest->id,
    ]);
});

it('redirects guests away from the cancel action', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->post(route('sauce-requests.cancel', $sauceRequest))
        ->assertRedirect(route('login'));
});

it('forbids a non-owner from canceling a draft', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($other)
        ->post(route('sauce-requests.cancel', $sauceRequest))
        ->assertForbidden();

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
    ]);
});

// ---------------------------------------------------------------------------
// Draft visibility
// ---------------------------------------------------------------------------

it('hides unpublished drafts from the public feed', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, [
        'title' => 'Published request',
        'published_at' => now(),
    ]);
    makeSauceRequest($owner, [
        'title' => 'Hidden draft',
        'published_at' => null,
    ]);

    $this->get(route('sauce-requests.index'))
        ->assertOk()
        ->assertSee('Published request')
        ->assertDontSee('Hidden draft');
});

it('hides unpublished drafts from the public show page', function () {
    $owner = User::factory()->create();
    $draft = makeSauceRequest($owner, [
        'published_at' => null,
    ]);

    $this->get(route('sauce-requests.show', $draft))
        ->assertNotFound();
});

it('allows the owner to preview their own unpublished draft', function () {
    $owner = User::factory()->create();
    $draft = makeSauceRequest($owner, [
        'title' => 'My draft',
        'published_at' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('sauce-requests.show', $draft))
        ->assertOk()
        ->assertSee('My draft');
});

// ---------------------------------------------------------------------------
// Opportunistic purge of abandoned drafts
// ---------------------------------------------------------------------------

it('purges abandoned drafts when visiting the upload form', function () {
    $owner = User::factory()->create();
    $abandoned = makeSauceRequest($owner, [
        'published_at' => null,
    ]);
    $abandoned->forceFill(['created_at' => now()->subHours(25)])->save();

    $recent = makeSauceRequest($owner, [
        'published_at' => null,
    ]);
    $recent->forceFill(['created_at' => now()->subHours(1)])->save();

    $this->actingAs($owner)
        ->get(route('create'))
        ->assertOk();

    $this->assertSoftDeleted('sauce_requests', ['id' => $abandoned->id]);
    $this->assertDatabaseHas('sauce_requests', ['id' => $recent->id]);
});

it('purges abandoned drafts when uploading a new image', function () {
    $owner = User::factory()->create();
    $abandoned = makeSauceRequest($owner, [
        'published_at' => null,
    ]);
    $abandoned->forceFill(['created_at' => now()->subHours(25)])->save();

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    $this->mock(OcrService::class)
        ->shouldReceive('extractText')
        ->once()
        ->andReturn('');

    $this->mock(TagInferenceService::class)
        ->shouldReceive('infer')
        ->once()
        ->andReturn([]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect();

    $this->assertSoftDeleted('sauce_requests', ['id' => $abandoned->id]);
});

it('does not purge published requests or drafts from other users', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $published = makeSauceRequest($owner);
    $published->forceFill(['created_at' => now()->subHours(25)])->save();

    $otherDraft = makeSauceRequest($other, [
        'published_at' => null,
    ]);
    $otherDraft->forceFill(['created_at' => now()->subHours(25)])->save();

    $this->actingAs($owner)
        ->get(route('create'))
        ->assertOk();

    $this->assertDatabaseHas('sauce_requests', ['id' => $published->id]);
    $this->assertDatabaseHas('sauce_requests', ['id' => $otherDraft->id]);
});