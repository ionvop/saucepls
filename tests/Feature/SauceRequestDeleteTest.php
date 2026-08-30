<?php

use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Authorization
// ---------------------------------------------------------------------------

it('redirects guests away from the delete action', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->delete(route('sauce-requests.destroy', $sauceRequest))
        ->assertRedirect(route('login'));
});

it('forbids a non-owner member from deleting the request', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($other)
        ->delete(route('sauce-requests.destroy', $sauceRequest))
        ->assertForbidden();

    $this->assertDatabaseHas('sauce_requests', [
        'id' => $sauceRequest->id,
        'deleted_at' => null,
    ]);
});

it('allows a moderator to delete a request owned by another user', function () {
    $owner = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($moderator)
        ->delete(route('sauce-requests.destroy', $sauceRequest))
        ->assertRedirect(route('sauce-requests.index'));

    $this->assertSoftDeleted('sauce_requests', ['id' => $sauceRequest->id]);
});

it('allows an admin to delete a request owned by another user', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['type' => 'admin']);
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($admin)
        ->delete(route('sauce-requests.destroy', $sauceRequest))
        ->assertRedirect(route('sauce-requests.index'));

    $this->assertSoftDeleted('sauce_requests', ['id' => $sauceRequest->id]);
});

// ---------------------------------------------------------------------------
// Deleting the request
// ---------------------------------------------------------------------------

it('allows the owner to delete their own published request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($owner)
        ->delete(route('sauce-requests.destroy', $sauceRequest))
        ->assertRedirect(route('sauce-requests.index'))
        ->assertSessionHas('status', 'Your sauce request has been deleted.');

    $this->assertSoftDeleted('sauce_requests', ['id' => $sauceRequest->id]);
});

it('allows the owner to delete their own draft', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'published_at' => null,
    ]);

    $this->actingAs($owner)
        ->delete(route('sauce-requests.destroy', $sauceRequest))
        ->assertRedirect(route('sauce-requests.index'));

    $this->assertSoftDeleted('sauce_requests', ['id' => $sauceRequest->id]);
});

it('removes the uploaded image file when deleting', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $file = 'sauce-requests/test.png';
    Storage::disk('public')->put($file, 'image-contents');
    $sauceRequest = makeSauceRequest($owner, ['image_path' => $file]);

    $this->actingAs($owner)
        ->delete(route('sauce-requests.destroy', $sauceRequest))
        ->assertRedirect(route('sauce-requests.index'));

    Storage::disk('public')->assertMissing($file);
    $this->assertSoftDeleted('sauce_requests', ['id' => $sauceRequest->id]);
});