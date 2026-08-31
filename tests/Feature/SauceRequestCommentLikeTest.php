<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestComment;
use App\Models\SauceRequestCommentLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Liking comments
// ---------------------------------------------------------------------------

it('lets any authenticated user like a comment', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceRequestCommentLike::count())->toBe(1);
    $like = SauceRequestCommentLike::firstOrFail();
    expect($like->sauce_request_comment_id)->toBe($comment->id);
    expect($like->user_id)->toBe($member->id);
});

it('lets any authenticated user like a reply', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $parent = makeComment($sauceRequest, $owner);
    $reply = makeComment($sauceRequest, $member, ['parent_id' => $parent->id]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.comments.like', [$sauceRequest, $reply]))
        ->assertRedirect();

    expect(SauceRequestCommentLike::where('sauce_request_comment_id', $reply->id)->count())->toBe(1);
});

it('does not create duplicate likes for the same user', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
        ->assertRedirect();

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
        ->assertRedirect();

    expect(SauceRequestCommentLike::count())->toBe(1);
});

it('redirects guests away from the like route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $owner);

    $this->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
        ->assertRedirect(route('login'));

    expect(SauceRequestCommentLike::count())->toBe(0);
});

it('rejects likes on unpublished drafts', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner, ['published_at' => null]);
    $comment = makeComment($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
        ->assertNotFound();

    expect(SauceRequestCommentLike::count())->toBe(0);
});

it('rejects a like on a comment from a different sauce request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $first = makeSauceRequest($owner);
    $second = makeSauceRequest($owner);
    $comment = makeComment($first, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.like', [$second, $comment]))
        ->assertStatus(422);

    expect(SauceRequestCommentLike::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Unliking comments
// ---------------------------------------------------------------------------

it('lets a user unlike a comment they liked', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
        ->assertRedirect();

    $this->actingAs($member)
        ->delete(route('sauce-requests.comments.unlike', [$sauceRequest, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(SauceRequestCommentLike::count())->toBe(0);
});

it('makes unliking idempotent', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $owner);

    $this->actingAs($member)
        ->delete(route('sauce-requests.comments.unlike', [$sauceRequest, $comment]))
        ->assertRedirect();

    expect(SauceRequestCommentLike::count())->toBe(0);
});

it('redirects guests away from the unlike route', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $member);

    $this->delete(route('sauce-requests.comments.unlike', [$sauceRequest, $comment]))
        ->assertRedirect(route('login'));
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 20 likes per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $owner);

    $this->actingAs($member);

    for ($i = 0; $i < 20; $i++) {
        $this->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
            ->assertRedirect();
    }

    $this->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
        ->assertTooManyRequests();
});

it('exempts staff from the like rate limit', function () {
    $owner = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeSauceRequest($owner);
    $comment = makeComment($sauceRequest, $owner);

    $this->actingAs($moderator);

    for ($i = 0; $i < 25; $i++) {
        $this->post(route('sauce-requests.comments.like', [$sauceRequest, $comment]))
            ->assertRedirect();
    }
});
