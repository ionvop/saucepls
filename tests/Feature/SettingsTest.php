<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Guest settings page
// ---------------------------------------------------------------------------

it('lets guests view the settings page', function () {
    $this->get(route('settings'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('Hide NSFW content')
        ->assertSee('guestNsfwToggle');
});

it('does not show the first-visit dialog on the settings page', function () {
    $this->get(route('settings'))
        ->assertOk()
        ->assertDontSee('explicitContentDialog');
});

it('does not render the account save form for guests', function () {
    $this->get(route('settings'))
        ->assertOk()
        ->assertDontSee('Save changes');
});

it('redirects guests who try to update settings to login', function () {
    $this->put(route('settings.update'), ['hide_nsfw' => true])
        ->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Authenticated settings page
// ---------------------------------------------------------------------------

it('lets authenticated users view the settings page', function () {
    $user = User::factory()->create(['hide_nsfw' => true]);

    $this->actingAs($user)
        ->get(route('settings'))
        ->assertOk()
        ->assertSee('Settings')
        ->assertSee('Save changes');
});

it('lets authenticated users update their hide_nsfw preference', function () {
    $user = User::factory()->create(['hide_nsfw' => false]);

    $this->actingAs($user)
        ->put(route('settings.update'), ['hide_nsfw' => true])
        ->assertRedirect(route('settings'))
        ->assertSessionHas('status');

    $this->assertTrue($user->fresh()->hide_nsfw);
});