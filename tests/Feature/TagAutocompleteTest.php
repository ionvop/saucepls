<?php

use App\Models\SauceRequest;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Attach the given tag name to a sauce request, creating the tag if needed.
 */
function tagSauceRequest(SauceRequest $sauceRequest, string $name): void
{
    $tag = Tag::firstOrCreate(['name' => $name]);
    $sauceRequest->tags()->syncWithoutDetaching($tag->id);
}

// ---------------------------------------------------------------------------
// The autocomplete endpoint

test('returns suggestions for a tag prefix', function () {
    $owner = User::factory()->create();

    $request = makeSauceRequest($owner);
    tagSauceRequest($request, 'blue_eyes');
    tagSauceRequest($request, 'red_eyes');
    tagSauceRequest($request, 'red_hair');

    $this->getJson(route('tags.autocomplete', ['q' => 're']))
        ->assertOk()
        ->assertJsonPath('tags.0.name', 'red_eyes')
        ->assertJsonPath('tags.1.name', 'red_hair')
        ->assertJsonCount(2, 'tags');
});

test('suggestions are sorted by usage count then alphabetically', function () {
    $owner = User::factory()->create();

    $a = makeSauceRequest($owner);
    tagSauceRequest($a, 'smile');
    tagSauceRequest($a, 'grin');

    $b = makeSauceRequest($owner);
    tagSauceRequest($b, 'smile');

    // When usage counts tie, the alphabetical order wins.
    $c = makeSauceRequest($owner);
    tagSauceRequest($c, 'grin');

    $this->getJson(route('tags.autocomplete', ['q' => 'sm']))
        ->assertOk()
        ->assertJsonPath('tags.0.name', 'smile')
        ->assertJsonPath('tags.0.usage_count', 2);

    $this->getJson(route('tags.autocomplete', ['q' => 'gr']))
        ->assertOk()
        ->assertJsonPath('tags.0.name', 'grin')
        ->assertJsonPath('tags.0.usage_count', 2);
});

test('suggestions only match at the start of the tag name', function () {
    $owner = User::factory()->create();

    $request = makeSauceRequest($owner);
    tagSauceRequest($request, 'blue_eyes');
    tagSauceRequest($request, 'red_eyes');

    // `eyes` is present but does not prefix-match; `blue`/`red` do.
    $this->getJson(route('tags.autocomplete', ['q' => 'eyes']))
        ->assertOk()
        ->assertJson(['tags' => []]);
});

test('short prefixes return no suggestions', function () {
    $this->getJson(route('tags.autocomplete', ['q' => 'r']))
        ->assertOk()
        ->assertJson(['tags' => []]);
});

test('missing query returns no suggestions', function () {
    $this->getJson(route('tags.autocomplete'))
        ->assertOk()
        ->assertJson(['tags' => []]);
});

test('returns at most ten suggestions', function () {
    $owner = User::factory()->create();
    $request = makeSauceRequest($owner);

    foreach (range(1, 15) as $n) {
        tagSauceRequest($request, 'tag'.str_pad((string) $n, 2, '0', STR_PAD_LEFT));
    }

    $response = $this->getJson(route('tags.autocomplete', ['q' => 'tag']))
        ->assertOk();

    $this->assertCount(10, $response->json('tags'));
});