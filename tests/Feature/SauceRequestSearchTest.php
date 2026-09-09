<?php

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use App\Models\SauceRequestBookmark;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

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

// ---------------------------------------------------------------------------
// Date prefixes (since / until / within)
// ---------------------------------------------------------------------------

it('filters to requests published on or after a since: date (UTC)', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'On the boundary', 'published_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-04-20 00:00:00', 'UTC')]);
    makeSauceRequest($owner, ['title' => 'Before the boundary', 'published_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-04-19 23:59:59', 'UTC')]);

    $this->get(route('search', ['q' => 'since:2026-04-20']))
        ->assertOk()
        ->assertSee('On the boundary')
        ->assertDontSee('Before the boundary');
});

it('filters to requests published on or before an until: date (UTC)', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'On the boundary', 'published_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-09-11 23:59:59', 'UTC')]);
    makeSauceRequest($owner, ['title' => 'After the boundary', 'published_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-09-12 00:00:00', 'UTC')]);

    $this->get(route('search', ['q' => 'until:2026-09-11']))
        ->assertOk()
        ->assertSee('On the boundary')
        ->assertDontSee('After the boundary');
});

it('filters to requests published within the last N days', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Recent', 'published_at' => now()->subDays(4)]);
    makeSauceRequest($owner, ['title' => 'Too old', 'published_at' => now()->subDays(6)]);

    $this->get(route('search', ['q' => 'within:5d']))
        ->assertOk()
        ->assertSee('Recent')
        ->assertDontSee('Too old');
});

it('supports weeks and hours for the within: prefix', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Within a week', 'published_at' => now()->subDays(6)]);
    makeSauceRequest($owner, ['title' => 'Older than a week', 'published_at' => now()->subDays(15)]);

    $this->get(route('search', ['q' => 'within:2w']))
        ->assertOk()
        ->assertSee('Within a week')
        ->assertDontSee('Older than a week');

    makeSauceRequest($owner, ['title' => 'Within hours', 'published_at' => now()->subHours(10)]);
    makeSauceRequest($owner, ['title' => 'Older than hours', 'published_at' => now()->subHours(14)]);

    $this->get(route('search', ['q' => 'within:12h']))
        ->assertOk()
        ->assertSee('Within hours')
        ->assertDontSee('Older than hours');
});

it('supports months and years for the within: prefix', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Within a month', 'published_at' => now()->subDays(20)]);
    makeSauceRequest($owner, ['title' => 'Older than a month', 'published_at' => now()->subDays(45)]);

    $this->get(route('search', ['q' => 'within:1m']))
        ->assertOk()
        ->assertSee('Within a month')
        ->assertDontSee('Older than a month');

    makeSauceRequest($owner, ['title' => 'Within a year', 'published_at' => now()->subMonths(6)]);
    makeSauceRequest($owner, ['title' => 'Older than a year', 'published_at' => now()->subMonths(14)]);

    $this->get(route('search', ['q' => 'within:1y']))
        ->assertOk()
        ->assertSee('Within a year')
        ->assertDontSee('Older than a year');
});

it('converts a since: date from the browser timezone to UTC', function () {
    $owner = User::factory()->create();

    // 2026-04-20 00:00:00 in America/New_York (-04:00) is 2026-04-20 04:00:00 UTC.
    makeSauceRequest($owner, ['title' => 'After NY midnight', 'published_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-04-20 04:00:00', 'UTC')]);
    makeSauceRequest($owner, ['title' => 'Before NY midnight', 'published_at' => Carbon::createFromFormat('Y-m-d H:i:s', '2026-04-20 03:59:59', 'UTC')]);

    $this->get(route('search', ['q' => 'since:2026-04-20', 'tz' => 'America/New_York']))
        ->assertOk()
        ->assertSee('After NY midnight')
        ->assertDontSee('Before NY midnight');
});

it('ignores invalid date and duration values', function () {
    $owner = User::factory()->create();
    makeSauceRequest($owner, ['title' => 'Original title']);

    $this->get(route('search', ['q' => 'since:not-a-date until:also-bad within:xyz']))
        ->assertOk()
        ->assertSee('Original title');
});

it('combines date prefixes with other search words', function () {
    $owner = User::factory()->create();
    $matching = makeSauceRequest($owner, ['title' => 'coconut doggy', 'published_at' => now()->subDays(2)]);
    attachTag($matching, '1girl');
    makeSauceRequest($owner, ['title' => 'coconut doggy', 'published_at' => now()->subDays(10)]);
    makeSauceRequest($owner, ['title' => 'coconut kitty', 'published_at' => now()->subDays(2)]);

    $this->get(route('search', ['q' => 'coconut tag:1girl within:5d']))
        ->assertOk()
        ->assertSee('coconut doggy')
        ->assertDontSee('coconut kitty');
});