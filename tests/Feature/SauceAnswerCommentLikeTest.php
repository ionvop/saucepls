<?php

use App\Models\SauceAnswer;
use App\Models\SauceAnswerCommentLike;
use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Liking comments
// ---------------------------------------------------------------------------

it('lets any authenticated user like an answer comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceAnswerCommentLike::count())->toBe(1);
    $like = SauceAnswerCommentLike::firstOrFail();
    expect($like->sauce_answer_comment_id)->toBe($comment->id);
    expect($like->user_id)->toBe($member->id);
});

it('does not create duplicate likes for the same user', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
        ->assertRedirect();

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
        ->assertRedirect();

    expect(SauceAnswerCommentLike::count())->toBe(1);
});

it('redirects guests away from the like route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
        ->assertRedirect(route('login'));

    expect(SauceAnswerCommentLike::count())->toBe(0);
});

it('rejects likes on unpublished drafts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, ['published_at' => null]);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
        ->assertNotFound();

    expect(SauceAnswerCommentLike::count())->toBe(0);
});

it('rejects a like on a comment from a different sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $answer = makeAnswer($first, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.like', [$second, $answer, $comment]))
        ->assertStatus(422);

    expect(SauceAnswerCommentLike::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Unliking comments
// ---------------------------------------------------------------------------

it('lets a user unlike an answer comment they liked', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
        ->assertRedirect();

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.comments.unlike', [$sauceRequest, $answer, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceAnswerCommentLike::count())->toBe(0);
});

it('makes unliking idempotent', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.comments.unlike', [$sauceRequest, $answer, $comment]))
        ->assertRedirect();

    expect(SauceAnswerCommentLike::count())->toBe(0);
});

it('redirects guests away from the unlike route', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $member);

    $this->delete(route('sauce-requests.answers.comments.unlike', [$sauceRequest, $answer, $comment]))
        ->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 20 likes per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($member);

    for ($i = 0; $i < 20; $i++) {
        $this->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
            ->assertRedirect();
    }

    $this->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
        ->assertTooManyRequests();
});

it('exempts staff from the like rate limit', function () {
    $owner = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $owner);

    $this->actingAs($moderator);

    for ($i = 0; $i < 25; $i++) {
        $this->post(route('sauce-requests.answers.comments.like', [$sauceRequest, $answer, $comment]))
            ->assertRedirect();
    }
});