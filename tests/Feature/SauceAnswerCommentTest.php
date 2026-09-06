<?php

use App\Models\SauceAnswer;
use App\Models\SauceAnswerComment;
use App\Models\SauceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a comment on the given sauce answer.
 */
function makeAnswerComment(SauceAnswer $answer, User $user, array $attributes = []): SauceAnswerComment
{
    return SauceAnswerComment::create(array_merge([
        'sauce_answer_id' => $answer->id,
        'user_id' => $user->id,
        'content' => 'Thanks for the source!',
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Posting comments
// ---------------------------------------------------------------------------

it('lets any authenticated user post a comment on an answer', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
            'content' => 'Thanks for the source!',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $comment = SauceAnswerComment::firstOrFail();
    expect($comment->sauce_answer_id)->toBe($answer->id);
    expect($comment->user_id)->toBe($member->id);
    expect($comment->content)->toBe('Thanks for the source!');
});

it('redirects guests away from the answer comment route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
        'content' => 'Hello',
    ])->assertRedirect(route('login'));
});

it('requires comment content', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
            'content' => '',
        ])
        ->assertSessionHasErrors('content');

    expect(SauceAnswerComment::count())->toBe(0);
});

it('rejects comments on unpublished drafts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, ['published_at' => null]);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
            'content' => 'Hello',
        ])
        ->assertNotFound();

    expect(SauceAnswerComment::count())->toBe(0);
});

it('rejects a comment on an answer from a different sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $answer = makeAnswer($first, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.comments.store', [$second, $answer]), [
            'content' => 'Wrong thread',
        ])
        ->assertStatus(422);

    expect(SauceAnswerComment::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Deleting comments
// ---------------------------------------------------------------------------

it('lets the author delete their own comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $member);

    $this->actingAs($member)
        ->delete(route('sauce-requests.answers.comments.destroy', [$sauceRequest, $answer, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('lets staff delete any answer comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $member);

    $this->actingAs($moderator)
        ->delete(route('sauce-requests.answers.comments.destroy', [$sauceRequest, $answer, $comment]))
        ->assertRedirect();

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('forbids a member from deleting someone elses comment', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $author);

    $this->actingAs($other)
        ->delete(route('sauce-requests.answers.comments.destroy', [$sauceRequest, $answer, $comment]))
        ->assertForbidden();

    expect($comment->fresh()->trashed())->toBeFalse();
});

it('redirects guests away from the delete route', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);
    $comment = makeAnswerComment($answer, $author);

    $this->delete(route('sauce-requests.answers.comments.destroy', [$sauceRequest, $answer, $comment]))
        ->assertRedirect(route('login'));

    expect($comment->fresh()->trashed())->toBeFalse();
});

it('rejects deleting a comment on an answer from a different sauce request', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $answer = makeAnswer($first, $owner);
    $comment = makeAnswerComment($answer, $author);

    $this->actingAs($author)
        ->delete(route('sauce-requests.answers.comments.destroy', [$second, $answer, $comment]))
        ->assertStatus(422);

    expect($comment->fresh()->trashed())->toBeFalse();
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 5 comments per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($member);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
            'content' => "comment {$i}",
        ])->assertRedirect();
    }

    $this->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
        'content' => 'comment 6',
    ])->assertTooManyRequests();
});