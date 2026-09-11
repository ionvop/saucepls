<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Static / informational pages
// ---------------------------------------------------------------------------

it('serves the About page', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('About');
});

it('serves the Contact page', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Contact');
});

it('serves the Terms of Service page', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('Terms of Service');
});

it('serves the Privacy Policy page', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Privacy Policy');
});

// ---------------------------------------------------------------------------
// Footer links
// ---------------------------------------------------------------------------

it('shows the footer links on the home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('About')
        ->assertSee('Contact')
        ->assertSee('Terms of Service')
        ->assertSee('Privacy Policy');
});