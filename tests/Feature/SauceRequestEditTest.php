<?php

use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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