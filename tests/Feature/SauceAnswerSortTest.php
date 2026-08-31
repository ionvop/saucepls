<?php

use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a sauce answer with the given number of likes from distinct users.
 */
function makeLikedAnswer(SauceRequest $sauceRequest, User $author, int $likes): \App\Models\SauceAnswer
{
    $answer = makeAnswer($sauceRequest, $author);

    for ($i = 0; $i < $likes; $i++) {
        $liker = User::factory()->create();
        $liker->sauceAnswerLikes()->create(['sauce_answer_id' => $answer->id]);
    }

    return $answer;
}

it('sorts answers by likes by default', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $low = makeLikedAnswer($sauceRequest, User::factory()->create(), 1);
    $high = makeLikedAnswer($sauceRequest, User::factory()->create(), 5);
    $mid = makeLikedAnswer($sauceRequest, User::factory()->create(), 3);

    $this->get(route('sauce-requests.show', $sauceRequest))
        ->assertOk()
        ->assertViewHas('sort', 'likes')
        ->assertViewHas('sauceRequest', function (SauceRequest $viewRequest) use ($high, $mid, $low) {
            $ids = $viewRequest->answers->pluck('id')->all();
            return $ids === [$high->id, $mid->id, $low->id];
        });
});

it('sorts answers by most recent when requested', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $first = makeLikedAnswer($sauceRequest, User::factory()->create(), 5);
    $second = makeLikedAnswer($sauceRequest, User::factory()->create(), 1);

    $this->get(route('sauce-requests.show', ['sauceRequest' => $sauceRequest, 'sort' => 'recent']))
        ->assertOk()
        ->assertViewHas('sort', 'recent')
        ->assertViewHas('sauceRequest', function (SauceRequest $viewRequest) use ($first, $second) {
            $ids = $viewRequest->answers->pluck('id')->all();
            return $ids === [$second->id, $first->id];
        });
});

it('falls back to likes for an unknown sort value', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->get(route('sauce-requests.show', ['sauceRequest' => $sauceRequest, 'sort' => 'bogus']))
        ->assertOk()
        ->assertViewHas('sort', 'likes');
});

it('pins the accepted answer to the top regardless of sort', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $accepted = makeLikedAnswer($sauceRequest, User::factory()->create(), 1);
    $popular = makeLikedAnswer($sauceRequest, User::factory()->create(), 9);

    $sauceRequest->update(['accepted_sauce' => $accepted->id]);

    // Even in "most liked" sort, the accepted answer comes first.
    $this->get(route('sauce-requests.show', $sauceRequest))
        ->assertOk()
        ->assertViewHas('sauceRequest', function (SauceRequest $viewRequest) use ($accepted, $popular) {
            $ids = $viewRequest->answers->pluck('id')->all();
            return $ids === [$accepted->id, $popular->id];
        });

    // And in "most recent" sort too.
    $this->get(route('sauce-requests.show', ['sauceRequest' => $sauceRequest, 'sort' => 'recent']))
        ->assertOk()
        ->assertViewHas('sauceRequest', function (SauceRequest $viewRequest) use ($accepted, $popular) {
            $ids = $viewRequest->answers->pluck('id')->all();
            return $ids === [$accepted->id, $popular->id];
        });
});