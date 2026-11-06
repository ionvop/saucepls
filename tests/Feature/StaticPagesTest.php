<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Static / informational pages
// ---------------------------------------------------------------------------

it('serves the About page', function () {
    $this->get(route('page.show', ['page' => 'about']))
        ->assertOk()
    ->assertSee('About Saucepls')
    ->assertSee('100 requests per day')
    ->assertSee('hosted live demo API')
    ->assertSee('href="https://github.com/Nanoskript/deepdanbooru-docker"', false)
    ->assertSee('href="https://deepdanbooru.nsk.sh/deepdanbooru"', false)
    ->assertSee('href="https://ocr.space/"', false)
    ->assertSee('href="https://ko-fi.com/ionvop"', false);
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
    $this->get(route('page.show', ['page' => 'privacy']))
        ->assertOk()
    ->assertSee('Privacy Policy')
    ->assertSee('SauceNAO')
    ->assertSee('OCR.space')
    ->assertSee('self-service account-deletion workflow')
    ->assertSee('[privacy contact email]');
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