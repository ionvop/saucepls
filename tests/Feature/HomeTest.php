<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestBookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a bookmark on the given sauce request by the given user.
 */
function bookmarkSauceRequest(SauceRequest $sauceRequest, User $user): void
{
    SauceRequestBookmark::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $user->id,
    ]);
}

// ---------------------------------------------------------------------------
// Guest home page
// ---------------------------------------------------------------------------

it('shows the hero and the three public sections to guests', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'A recent request']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Find the sauce behind any image.')
        ->assertSee('Popular this month')
        ->assertSee('Trending')
        ->assertSee('Recent')
        ->assertDontSee('Subscription feed');
});

it('does not show the subscription feed to guests', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'A recent request']);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Subscription feed');
});

// ---------------------------------------------------------------------------
// Authenticated home page
// ---------------------------------------------------------------------------

it('shows the subscription feed to authenticated users', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $viewer->following()->attach($followed->id);

    makeSauceRequest($followed, ['title' => 'From someone I follow']);

    $this->actingAs($viewer)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Subscription feed')
        ->assertSee('From someone I follow');
});

it('does not show the hero to authenticated users', function () {
    $viewer = User::factory()->create();

    $this->actingAs($viewer)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Find the sauce behind any image.');
});

it('only includes requests from followed users in the subscription feed', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $stranger = User::factory()->create();
    $viewer->following()->attach($followed->id);

    $followedRequest = makeSauceRequest($followed, ['title' => 'From someone I follow']);
    $strangerRequest = makeSauceRequest($stranger, ['title' => 'From a stranger']);

    $response = $this->actingAs($viewer)->get(route('home'));

    $response->assertOk();
    expect($response->viewData('subscriptionFeed')->pluck('id')->all())
        ->toBe([$followedRequest->id]);
    expect($response->viewData('subscriptionFeed')->pluck('id')->all())
        ->not->toContain($strangerRequest->id);
});

// ---------------------------------------------------------------------------
// Section contents
// ---------------------------------------------------------------------------

it('only includes requests published within the past month in popular this month', function () {
    $owner = User::factory()->create();
    $withinMonth = makeSauceRequest($owner, ['title' => 'Within a month', 'published_at' => now()->subDays(20)]);
    $older = makeSauceRequest($owner, ['title' => 'Older than a month', 'published_at' => now()->subMonths(2)]);

    $response = $this->get(route('home'));

    $response->assertOk();
    expect($response->viewData('popularThisMonth')->pluck('id')->all())
        ->toBe([$withinMonth->id]);
    expect($response->viewData('popularThisMonth')->pluck('id')->all())
        ->not->toContain($older->id);
});

it('sorts popular this month by bookmark count', function () {
    $owner = User::factory()->create();
    $popular = makeSauceRequest($owner, ['title' => 'Most bookmarked']);
    $lessPopular = makeSauceRequest($owner, ['title' => 'Less bookmarked']);

    bookmarkSauceRequest($popular, $owner);
    bookmarkSauceRequest($popular, User::factory()->create());
    bookmarkSauceRequest($lessPopular, $owner);

    $response = $this->get(route('home'));

    $response->assertOk();
    expect($response->viewData('popularThisMonth')->pluck('id')->all())
        ->toBe([$popular->id, $lessPopular->id]);
});

it('sorts trending by bookmarks received within the past week', function () {
    $owner = User::factory()->create();
    $trending = makeSauceRequest($owner, ['title' => 'Trending now']);
    $stale = makeSauceRequest($owner, ['title' => 'Stale']);

    // created_at is not fillable on SauceRequestBookmark, so set it directly
    // to control whether a bookmark counts toward the weekly window.
    (new SauceRequestBookmark([
        'sauce_request_id' => $trending->id,
        'user_id' => $owner->id,
    ]))->forceFill(['created_at' => now()->subDay()])->save();

    (new SauceRequestBookmark([
        'sauce_request_id' => $stale->id,
        'user_id' => $owner->id,
    ]))->forceFill(['created_at' => now()->subWeeks(2)])->save();

    $response = $this->get(route('home'));

    $response->assertOk();
    expect($response->viewData('trending')->pluck('id')->all())
        ->toBe([$trending->id, $stale->id]);
});

it('sorts recent by newest published first', function () {
    $owner = User::factory()->create();
    $newer = makeSauceRequest($owner, ['title' => 'Newer', 'published_at' => now()->subDay()]);
    $older = makeSauceRequest($owner, ['title' => 'Older', 'published_at' => now()->subDays(2)]);

    $response = $this->get(route('home'));

    $response->assertOk();
    expect($response->viewData('recent')->pluck('id')->all())
        ->toBe([$newer->id, $older->id]);
});

it('only includes published requests', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Published one']);
    makeSauceRequest($owner, ['title' => 'Hidden draft', 'published_at' => null]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Published one')
        ->assertDontSee('Hidden draft');
});

// ---------------------------------------------------------------------------
// NSFW filtering
// ---------------------------------------------------------------------------

it('hides explicit results for guests with the hide_nsfw cookie', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Explicit one', 'is_explicit' => true]);
    makeSauceRequest($owner, ['title' => 'Safe one', 'is_explicit' => false]);

    $this->withCookie('hide_nsfw', '1')
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Explicit one')
        ->assertSee('Safe one');
});

it('honors the authenticated user hide_nsfw preference', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create(['hide_nsfw' => true]);
    makeSauceRequest($owner, ['title' => 'Explicit one', 'is_explicit' => true]);

    $this->actingAs($viewer)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Explicit one');
});

// ---------------------------------------------------------------------------
// View all links
// ---------------------------------------------------------------------------

it('links popular this month to search within the last month sorted by popular', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('search', ['q' => 'within:1m', 'sort' => 'popular']));
});

it('links trending to search sorted by trending', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('search', ['sort' => 'trending']));
});

it('links recent to search', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('search'));
});

it('links the subscription feed to the subscriptions page', function () {
    $viewer = User::factory()->create();

    $this->actingAs($viewer)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('subscriptions'));
});