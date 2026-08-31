<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a top-level comment on the given sauce request.
 */
function makeComment(SauceRequest $sauceRequest, User $user, array $attributes = []): SauceRequestComment
{
    return SauceRequestComment::create(array_merge([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $user->id,
        'parent_id' => null,
        'content' => 'This looks like the artstyle of Snale on Twitter.',
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Posting comments
// ---------------------------------------------------------------------------

it('lets any authenticated user post a comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => 'This looks like the artstyle of Snale on Twitter.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $comment = SauceRequestComment::firstOrFail();
    expect($comment->sauce_request_id)->toBe($sauceRequest->id);
    expect($comment->user_id)->toBe($member->id);
    expect($comment->parent_id)->toBeNull();
    expect($comment->content)->toBe('This looks like the artstyle of Snale on Twitter.');
});

it('redirects guests away from the comment route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->post(route('sauce-requests.comments.store', $sauceRequest), [
        'content' => 'Hello',
    ])->assertRedirect(route('login'));
});

it('requires comment content', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => '',
        ])
        ->assertSessionHasErrors('content');

    expect(SauceRequestComment::count())->toBe(0);
});

it('rejects comments on unpublished drafts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, ['published_at' => null]);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => 'Hello',
        ])
        ->assertNotFound();

    expect(SauceRequestComment::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Replies
// ---------------------------------------------------------------------------

it('lets any authenticated user reply to a top-level comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $parent = makeComment($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => 'Thanks for the source!',
            'parent_id' => $parent->id,
        ])
        ->assertRedirect();

    $reply = SauceRequestComment::where('parent_id', $parent->id)->firstOrFail();
    expect($reply->user_id)->toBe($member->id);
    expect($reply->content)->toBe('Thanks for the source!');
});

it('rejects a reply to a comment on a different sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $parent = makeComment($first, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.store', $second), [
            'content' => 'Wrong thread',
            'parent_id' => $parent->id,
        ])
        ->assertStatus(422);

    expect(SauceRequestComment::count())->toBe(1);
});

it('rejects a reply to a reply to keep threads one level deep', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $parent = makeComment($sauceRequest, $owner);
    $reply = makeComment($sauceRequest, $member, ['parent_id' => $parent->id]);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => 'Nested too deep',
            'parent_id' => $reply->id,
        ])
        ->assertStatus(422);

    expect(SauceRequestComment::count())->toBe(2);
});

// ---------------------------------------------------------------------------
// Deleting comments
// ---------------------------------------------------------------------------

it('lets the author delete their own comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $member);

    $this->actingAs($member)
        ->delete(route('sauce-requests.comments.destroy', [$sauceRequest, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('lets staff delete any comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $member);

    $this->actingAs($moderator)
        ->delete(route('sauce-requests.comments.destroy', [$sauceRequest, $comment]))
        ->assertRedirect();

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('forbids a member from deleting someone elses comment', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $other = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $author);

    $this->actingAs($other)
        ->delete(route('sauce-requests.comments.destroy', [$sauceRequest, $comment]))
        ->assertForbidden();

    expect($comment->fresh()->trashed())->toBeFalse();
});

it('redirects guests away from the delete route', function () {
    $owner = User::factory()->create();
    $author = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $author);

    $this->delete(route('sauce-requests.comments.destroy', [$sauceRequest, $comment]))
        ->assertRedirect(route('login'));

    expect($comment->fresh()->trashed())->toBeFalse();
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 5 comments per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => "comment {$i}",
        ])->assertRedirect();
    }

    $this->post(route('sauce-requests.comments.store', $sauceRequest), [
        'content' => 'comment 6',
    ])->assertTooManyRequests();
});