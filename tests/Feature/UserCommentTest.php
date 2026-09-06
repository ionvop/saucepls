<?php

use App\Models\User;
use App\Models\UserComment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a comment on the given user's profile.
 */
function makeProfileComment(User $profileUser, User $author, array $attributes = []): UserComment
{
    return UserComment::create(array_merge([
        'profile_user_id' => $profileUser->id,
        'user_id' => $author->id,
        'content' => 'Nice profile!',
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Posting comments
// ---------------------------------------------------------------------------

it('lets any authenticated user post a comment on a profile', function () {
    $profileUser = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('profile.comments.store', $profileUser), [
            'content' => 'Nice profile!',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $comment = UserComment::firstOrFail();
    expect($comment->profile_user_id)->toBe($profileUser->id);
    expect($comment->user_id)->toBe($member->id);
    expect($comment->parent_id)->toBeNull();
    expect($comment->content)->toBe('Nice profile!');
});

it('redirects guests away from the profile comment route', function () {
    $profileUser = User::factory()->create();

    $this->post(route('profile.comments.store', $profileUser), [
        'content' => 'Hello',
    ])->assertRedirect(route('login'));
});

it('requires comment content', function () {
    $profileUser = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($member)
        ->post(route('profile.comments.store', $profileUser), [
            'content' => '',
        ])
        ->assertSessionHasErrors('content');

    expect(UserComment::count())->toBe(0);
});

it('lets a user reply to a top-level comment', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $replier = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($replier)
        ->post(route('profile.comments.store', $profileUser), [
            'content' => 'I agree!',
            'parent_id' => $comment->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $reply = UserComment::where('parent_id', $comment->id)->firstOrFail();
    expect($reply->profile_user_id)->toBe($profileUser->id);
    expect($reply->user_id)->toBe($replier->id);
    expect($reply->content)->toBe('I agree!');
});

it('rejects a reply to a reply to keep threads one level deep', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $replier = User::factory()->create();
    $other = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);
    $reply = makeProfileComment($profileUser, $replier, ['parent_id' => $comment->id]);

    $this->actingAs($other)
        ->post(route('profile.comments.store', $profileUser), [
            'content' => 'Too deep',
            'parent_id' => $reply->id,
        ])
        ->assertStatus(422);

    expect(UserComment::count())->toBe(2);
});

it('rejects a reply to a comment on a different profile', function () {
    $firstProfile = User::factory()->create();
    $secondProfile = User::factory()->create();
    $author = User::factory()->create();
    $replier = User::factory()->create();
    $comment = makeProfileComment($firstProfile, $author);

    $this->actingAs($replier)
        ->post(route('profile.comments.store', $secondProfile), [
            'content' => 'Wrong profile',
            'parent_id' => $comment->id,
        ])
        ->assertStatus(422);

    expect(UserComment::count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Deleting comments
// ---------------------------------------------------------------------------

it('lets the author delete their own comment', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($author)
        ->delete(route('profile.comments.destroy', [$profileUser, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('lets staff delete any profile comment', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($moderator)
        ->delete(route('profile.comments.destroy', [$profileUser, $comment]))
        ->assertRedirect();

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('lets the profile owner delete a comment on their profile', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($profileUser)
        ->delete(route('profile.comments.destroy', [$profileUser, $comment]))
        ->assertRedirect();

    expect($comment->fresh()->trashed())->toBeTrue();
});

it('forbids a member from deleting someone elses comment', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $other = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->actingAs($other)
        ->delete(route('profile.comments.destroy', [$profileUser, $comment]))
        ->assertForbidden();

    expect($comment->fresh()->trashed())->toBeFalse();
});

it('redirects guests away from the delete route', function () {
    $profileUser = User::factory()->create();
    $author = User::factory()->create();
    $comment = makeProfileComment($profileUser, $author);

    $this->delete(route('profile.comments.destroy', [$profileUser, $comment]))
        ->assertRedirect(route('login'));

    expect($comment->fresh()->trashed())->toBeFalse();
});

it('rejects deleting a comment on a different profile', function () {
    $firstProfile = User::factory()->create();
    $secondProfile = User::factory()->create();
    $author = User::factory()->create();
    $comment = makeProfileComment($firstProfile, $author);

    $this->actingAs($author)
        ->delete(route('profile.comments.destroy', [$secondProfile, $comment]))
        ->assertStatus(422);

    expect($comment->fresh()->trashed())->toBeFalse();
});

// ---------------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------------

it('rate limits a member to 5 comments per minute', function () {
    $profileUser = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($member);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('profile.comments.store', $profileUser), [
            'content' => "comment {$i}",
        ])->assertRedirect();
    }

    $this->post(route('profile.comments.store', $profileUser), [
        'content' => 'comment 6',
    ])->assertTooManyRequests();
});