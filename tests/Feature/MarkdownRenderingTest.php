<?php

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Markdown rendering for sauce request descriptions & answer content
// ---------------------------------------------------------------------------

it('renders markdown in a sauce request description', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'description' => "# Heading\n\nThis is **bold**.",
    ]);

    $this->get(route('sauce-requests.show', $sauceRequest))
        ->assertOk()
        ->assertSee('<h1', false)
        ->assertSee('<strong>bold</strong>', false);
});

it('renders markdown in a sauce answer content', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    makeAnswer($sauceRequest, $member, [
        'content' => "**Artist:** Snale\n\n[Source](https://example.com)",
    ]);

    $this->get(route('sauce-requests.show', $sauceRequest))
        ->assertOk()
        ->assertSee('<strong>Artist:</strong>', false)
        ->assertSee('href="https://example.com"', false);
});

it('escapes raw HTML in a sauce request description', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, [
        'description' => 'Hello <script>alert(1)</script>',
    ]);

    $this->get(route('sauce-requests.show', $sauceRequest))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;', false);
});

it('escapes raw HTML in a sauce answer content', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    makeAnswer($sauceRequest, $member, [
        'content' => 'Hello <script>alert(1)</script>',
    ]);

    $this->get(route('sauce-requests.show', $sauceRequest))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;', false);
});

it('renders markdown in answer content on the profile page', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    makeAnswer($sauceRequest, $member, [
        'content' => "**Artist:** Snale",
    ]);

    $this->get(route('profile.show', $member->username))
        ->assertOk()
        ->assertSee('<strong>Artist:</strong>', false);
});