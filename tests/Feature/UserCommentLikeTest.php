<?php

use App\Models\User;
use App\Models\UserCommentLike;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Liking comments
// ---------------------------------------------------------------------------

it('lets any authenticated user like a profile comment', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $member = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($member)
        ->post(route('profile.comments.like', [$profileUser, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(UserCommentLike::count())->toBe(1);
    $like = UserCommentLike::firstOrFail();
    expect($like->user_comment_id)->toBe($comment->id);
    expect($like->user_id)->toBe($member->id);
});

it('does not create duplicate likes for the same user', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $member = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($member)
        ->post(route('profile.comments.like', [$profileUser, $comment]))
        ->assertRedirect();

    $this->actingAs($member)
        ->post(route('profile.comments.like', [$profileUser, $comment]))
        ->assertRedirect();

    expect(UserCommentLike::count())->toBe(1);
});

it('redirects guests away from the like route', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->post(route('profile.comments.like', [$profileUser, $comment]))
        ->assertRedirect(route('login'));

    expect(UserCommentLike::count())->toBe(0);
});

it('rejects a like on a comment on a different profile', function () {
    $firstProfile = User::factory()->create();
    $secondProfile = User::factory()->create();
    $author = User::factory()->create();
    $member = User::factory()->create();
    $comment = makeProfileComment($firstProfile, $author);

    $this->actingAs($member)
        ->post(route('profile.comments.like', [$secondProfile, $comment]))
        ->assertStatus(422);

    expect(UserCommentLike::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Unliking comments
// ---------------------------------------------------------------------------

it('lets a user unlike a profile comment they liked', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $member = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($member)
        ->post(route('profile.comments.like', [$profileUser, $comment]))
        ->assertRedirect();

    $this->actingAs($member)
        ->delete(route('profile.comments.unlike', [$profileUser, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(UserCommentLike::count())->toBe(0);
});

it('makes unliking idempotent', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $member = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($member)
        ->delete(route('profile.comments.unlike', [$profileUser, $comment]))
        ->assertRedirect();

    expect(UserCommentLike::count())->toBe(0);
});

it('redirects guests away from the unlike route', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->delete(route('profile.comments.unlike', [$profileUser, $comment]))
        ->assertRedirect(route('login'));

    expect(UserCommentLike::count())->toBe(0);
});