<?php

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use App\Models\SauceRequestBookmark;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Attach the given tag name to a sauce request.
 */
function attachTag(SauceRequest $sauceRequest, string $name): void
{
    $tag = Tag::firstOrCreate(['name' => $name]);
    $sauceRequest->tags()->syncWithoutDetaching($tag->id);
}

/**
 * Create a sauce answer on the given sauce request and mark it as the
 * accepted answer so the request is solved.
 */
function solveSauceRequest(SauceRequest $sauceRequest, User $user): SauceAnswer
{
    $answer = SauceAnswer::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $user->id,
        'content' => 'Artist is Snale.',
        'url' => null,
    ]);

    $sauceRequest->update(['accepted_sauce' => $answer->id]);

    return $answer;
}

// ---------------------------------------------------------------------------
// The search page
// ---------------------------------------------------------------------------

it('redirects the old /sauce-requests index to the search page', function () {
    $this->get('/sauce-requests')
        ->assertRedirect(route('search'));
});

it('renders an empty state when no keyword is provided', function () {
    $this->get(route('search'))
        ->assertOk()
        ->assertSee('Search');
});

it('only returns published sauce requests', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner);
    makeSauceRequest($owner, ['published_at' => null, 'title' => 'Hidden draft']);

    $this->get(route('search'))
        ->assertOk()
        ->assertSee('Original title')
        ->assertDontSee('Hidden draft');
});

// ---------------------------------------------------------------------------
// Keyword search
// ---------------------------------------------------------------------------

it('finds requests by title, description, or extracted text', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Blue hair girl', 'description' => 'From an anime OVA', 'text' => '']);
    makeSauceRequest($owner, ['title' => 'Random cat', 'description' => 'Not relevant', 'text' => '']);

    $this->get(route('search', ['q' => 'anime']))
        ->assertOk()
        ->assertSee('Blue hair girl')
        ->assertDontSee('Random cat');
});

it('finds requests by tag name', function () {
    $owner = User::factory()->create();
    $tagged = makeSauceRequest($owner, ['title' => 'Tagged image']);
    $untagged = makeSauceRequest($owner, ['title' => 'Plain image']);
    attachTag($tagged, '1girl');

    $this->get(route('search', ['q' => '1girl']))
        ->assertOk()
        ->assertSee('Tagged image')
        ->assertDontSee('Plain image');
});

it('requires all words to match', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'coconut doggy', 'description' => '', 'text' => '']);
    makeSauceRequest($owner, ['title' => 'coconut kitty', 'description' => '', 'text' => '']);

    $this->get(route('search', ['q' => 'coconut doggy']))
        ->assertOk()
        ->assertSee('coconut doggy')
        ->assertDontSee('coconut kitty');
});

it('supports exact phrase matches with quotes', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'coconut doggy', 'description' => '', 'text' => '']);
    makeSauceRequest($owner, ['title' => 'coconut puppy doggy', 'description' => '', 'text' => '']);

    $this->get(route('search', ['q' => '"coconut doggy"']))
        ->assertOk()
        ->assertSee('coconut doggy');
});

it('excludes results containing a hyphen-prefixed word', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'girl with cat', 'description' => '', 'text' => '']);
    makeSauceRequest($owner, ['title' => 'girl with dog', 'description' => '', 'text' => '']);

    $this->get(route('search', ['q' => 'girl -cat']))
        ->assertOk()
        ->assertSee('girl with dog')
        ->assertDontSee('girl with cat');
});

it('narrows a word to the tags field with a tag: prefix', function () {
    $owner = User::factory()->create();
    $tagged = makeSauceRequest($owner, ['title' => 'Has the tag', 'description' => '', 'text' => '']);
    $onlyInTitle = makeSauceRequest($owner, ['title' => 'has the tag nowhere', 'description' => '', 'text' => '']);
    attachTag($tagged, '1girl');

    $this->get(route('search', ['q' => 'tag:1girl']))
        ->assertOk()
        ->assertSee('Has the tag')
        ->assertDontSee('has the tag nowhere');
});

it('narrows a word to the extracted text with a text: prefix', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'No text', 'text' => 'nothing relevant']);
    makeSauceRequest($owner, ['title' => 'Has text', 'text' => 'the sign says coconut']);

    $this->get(route('search', ['q' => 'text:coconut']))
        ->assertOk()
        ->assertSee('Has text')
        ->assertDontSee('No text');
});

it('matches the title field by default as a substring', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Substring match here', 'description' => '', 'text' => '']);

    $this->get(route('search', ['q' => 'string']))
        ->assertOk()
        ->assertSee('Substring match here');
});

// ---------------------------------------------------------------------------
// Solved / unsolved filter
// ---------------------------------------------------------------------------

it('filters to solved requests only', function () {
    $owner = User::factory()->create();
    $solved = makeSauceRequest($owner, ['title' => 'Solved one']);
    $unsolved = makeSauceRequest($owner, ['title' => 'Unsolved one']);
    solveSauceRequest($solved, $owner);

    $this->get(route('search', ['filter' => 'solved']))
        ->assertOk()
        ->assertSee('Solved one')
        ->assertDontSee('Unsolved one');
});

it('filters to unsolved requests only', function () {
    $owner = User::factory()->create();
    $solved = makeSauceRequest($owner, ['title' => 'Solved one']);
    $unsolved = makeSauceRequest($owner, ['title' => 'Unsolved one']);
    solveSauceRequest($solved, $owner);

    $this->get(route('search', ['filter' => 'unsolved']))
        ->assertOk()
        ->assertSee('Unsolved one')
        ->assertDontSee('Solved one');
});

it('defaults the filter to all', function () {
    $owner = User::factory()->create();
    $solved = makeSauceRequest($owner, ['title' => 'Solved one']);
    $unsolved = makeSauceRequest($owner, ['title' => 'Unsolved one']);
    solveSauceRequest($solved, $owner);

    $this->get(route('search'))
        ->assertOk()
        ->assertSee('Solved one')
        ->assertSee('Unsolved one');
});

// ---------------------------------------------------------------------------
// Sorting
// ---------------------------------------------------------------------------

it('sorts by most recent by default (newest published first)', function () {
    $owner = User::factory()->create();
    $older = makeSauceRequest($owner, ['title' => 'Older first', 'published_at' => now()->subDays(2)]);
    $newer = makeSauceRequest($owner, ['title' => 'Newer second', 'published_at' => now()]);

    $response = $this->get(route('search'));

    $response->assertOk();
    expect($response->viewData('sauceRequests')->pluck('id')->all())
        ->toBe([$newer->id, $older->id]);
});

it('sorts by bookmark count when choosing popular', function () {
    $owner = User::factory()->create();
    $popular = makeSauceRequest($owner, ['title' => 'Most bookmarked']);
    $lessPopular = makeSauceRequest($owner, ['title' => 'Less bookmarked']);

    SauceRequestBookmark::create(['sauce_request_id' => $popular->id, 'user_id' => $owner->id]);
    SauceRequestBookmark::create(['sauce_request_id' => $popular->id, 'user_id' => User::factory()->create()->id]);
    SauceRequestBookmark::create(['sauce_request_id' => $lessPopular->id, 'user_id' => $owner->id]);

    $response = $this->get(route('search', ['sort' => 'popular']));

    $response->assertOk();
    expect($response->viewData('sauceRequests')->pluck('id')->all())
        ->toBe([$popular->id, $lessPopular->id]);
});

it('sorts by bookmarks received within the past week when choosing trending', function () {
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

    $response = $this->get(route('search', ['sort' => 'trending']));

    $response->assertOk();
    expect($response->viewData('sauceRequests')->pluck('id')->all())
        ->toBe([$trending->id, $stale->id]);
});

it('ignores invalid sort and invalid filter values', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Original title']);

    $this->get(route('search', ['sort' => 'bogus', 'filter' => 'bogus']))
        ->assertOk();
});

// ---------------------------------------------------------------------------
// NSFW filtering
// ---------------------------------------------------------------------------

it('hides explicit results for guests with the hide_nsfw cookie', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Explicit one', 'is_explicit' => true]);
    makeSauceRequest($owner, ['title' => 'Safe one', 'is_explicit' => false]);

    $this->withCookie('hide_nsfw', '1')
        ->get(route('search'))
        ->assertOk()
        ->assertDontSee('Explicit one')
        ->assertSee('Safe one');
});

it('honors the authenticated user hide_nsfw preference', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create(['hide_nsfw' => true]);
    makeSauceRequest($owner, ['title' => 'Explicit one', 'is_explicit' => true]);

    $this->actingAs($viewer)
        ->get(route('search'))
        ->assertOk()
        ->assertDontSee('Explicit one');
});