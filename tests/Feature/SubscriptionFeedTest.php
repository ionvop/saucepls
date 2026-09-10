<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Access control
// ---------------------------------------------------------------------------

it('redirects guests to the login page', function () {
    $this->get(route('subscriptions'))
        ->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Left panel: followed users
// ---------------------------------------------------------------------------

it('lists the users the viewer follows in the left panel', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create(['username' => 'alice']);
    $viewer->following()->attach($followed->id);

    $this->actingAs($viewer)
        ->get(route('subscriptions'))
        ->assertOk()
        ->assertSee('Following')
        ->assertSee('alice');
});

it('shows an empty state in the left panel when following no one', function () {
    $viewer = User::factory()->create();

    $this->actingAs($viewer)
        ->get(route('subscriptions'))
        ->assertOk()
        ->assertSee('following anyone yet');
});

// ---------------------------------------------------------------------------
// Main feed
// ---------------------------------------------------------------------------

it('only includes published requests from followed users, newest first', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $stranger = User::factory()->create();
    $viewer->following()->attach($followed->id);

    $older = makeSauceRequest($followed, ['title' => 'Older post', 'published_at' => now()->subDays(2)]);
    $newer = makeSauceRequest($followed, ['title' => 'Newer post', 'published_at' => now()->subDay()]);
    $strangerRequest = makeSauceRequest($stranger, ['title' => 'From a stranger']);

    $response = $this->actingAs($viewer)->get(route('subscriptions'));

    $response->assertOk();
    expect($response->viewData('sauceRequests')->pluck('id')->all())
        ->toBe([$newer->id, $older->id]);
    expect($response->viewData('sauceRequests')->pluck('id')->all())
        ->not->toContain($strangerRequest->id);
});

it('excludes drafts from the feed', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $viewer->following()->attach($followed->id);

    makeSauceRequest($followed, ['title' => 'A draft', 'published_at' => null]);

    $response = $this->actingAs($viewer)->get(route('subscriptions'));

    $response->assertOk();
    expect($response->viewData('sauceRequests'))->toBeEmpty();
});

it('shows an empty state when followed users have no published requests', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $viewer->following()->attach($followed->id);

    $this->actingAs($viewer)
        ->get(route('subscriptions'))
        ->assertOk()
        ->assertSee('Follow users to see their posts here.');
});

it('hides explicit requests when the viewer has opted out of NSFW', function () {
    $viewer = User::factory()->create(['hide_nsfw' => true]);
    $followed = User::factory()->create();
    $viewer->following()->attach($followed->id);

    $explicit = makeSauceRequest($followed, ['title' => 'Explicit post', 'is_explicit' => true]);
    $safe = makeSauceRequest($followed, ['title' => 'Safe post', 'is_explicit' => false]);

    $response = $this->actingAs($viewer)->get(route('subscriptions'));

    $response->assertOk();
    expect($response->viewData('sauceRequests')->pluck('id')->all())
        ->toBe([$safe->id]);
    expect($response->viewData('sauceRequests')->pluck('id')->all())
        ->not->toContain($explicit->id);
});

it('paginates the feed', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $viewer->following()->attach($followed->id);

    foreach (range(1, 13) as $i) {
        makeSauceRequest($followed, ['title' => "Post {$i}", 'published_at' => now()->subMinutes($i)]);
    }

    $response = $this->actingAs($viewer)->get(route('subscriptions'));

    $response->assertOk();
    expect($response->viewData('sauceRequests')->total())->toBe(13);
    expect($response->viewData('sauceRequests')->count())->toBe(12);
});
