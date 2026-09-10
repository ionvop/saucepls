<?php

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use App\Models\SauceRequestBookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Assert that the given user has exactly one notification of the given type.
 */
function assertNotified(User $user, string $type): DatabaseNotification
{
    $notification = $user->notifications()->where('type', $type)->first();

    expect($notification)->not->toBeNull("Expected user {$user->id} to have a {$type} notification.");

    return $notification;
}

/**
 * Assert that the given user has no notification of the given type.
 */
function assertNotNotified(User $user, string $type): void
{
    expect($user->notifications()->where('type', $type)->count())->toBe(0);
}

// ---------------------------------------------------------------------------
// Answer posted
// ---------------------------------------------------------------------------

it('notifies the request author when someone answers their request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'Artist is Snale.',
        ])
        ->assertRedirect();

    $notification = assertNotified($owner, 'new_answer');
    expect($notification->data['actor_id'])->toBe($member->id);
    expect($notification->data['sauce_request_id'])->toBe($sauceRequest->id);
});

it('does not notify the request author when they answer their own request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($owner)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'Artist is Snale.',
        ])
        ->assertRedirect();

    assertNotNotified($owner, 'new_answer');
});

// ---------------------------------------------------------------------------
// Comment on a sauce request
// ---------------------------------------------------------------------------

it('notifies the request author when someone comments on their request', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => 'Nice request!',
        ])
        ->assertRedirect();

    $notification = assertNotified($owner, 'new_request_comment');
    expect($notification->data['actor_id'])->toBe($member->id);
    expect($notification->data['sauce_request_id'])->toBe($sauceRequest->id);
});

it('does not notify the request author when they comment on their own request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($owner)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => 'Bump.',
        ])
        ->assertRedirect();

    assertNotNotified($owner, 'new_request_comment');
});

it('notifies the parent comment author when someone replies to their comment', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $replier = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $parent = \App\Models\SauceRequestComment::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $commenter->id,
        'parent_id' => null,
        'content' => 'Top-level comment.',
    ]);

    $this->actingAs($replier)
        ->post(route('sauce-requests.comments.store', $sauceRequest), [
            'content' => 'A reply.',
            'parent_id' => $parent->id,
        ])
        ->assertRedirect();

    $notification = assertNotified($commenter, 'new_request_comment');
    expect($notification->data['actor_id'])->toBe($replier->id);
    expect($notification->data['parent_id'])->toBe($parent->id);
});

// ---------------------------------------------------------------------------
// Comment on an answer
// ---------------------------------------------------------------------------

it('notifies the answer author and request author when someone comments on an answer', function () {
    $owner = User::factory()->create();
    $answerer = User::factory()->create();
    $commenter = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $answerer);

    $this->actingAs($commenter)
        ->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
            'content' => 'Thanks for the source!',
        ])
        ->assertRedirect();

    $answerNotification = assertNotified($answerer, 'new_answer_comment');
    expect($answerNotification->data['actor_id'])->toBe($commenter->id);

    $requestNotification = assertNotified($owner, 'new_answer_comment');
    expect($requestNotification->data['actor_id'])->toBe($commenter->id);
});

it('does not notify the answer author when they comment on their own answer', function () {
    $owner = User::factory()->create();
    $answerer = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $answerer);

    $this->actingAs($answerer)
        ->post(route('sauce-requests.answers.comments.store', [$sauceRequest, $answer]), [
            'content' => 'Self comment.',
        ])
        ->assertRedirect();

    assertNotNotified($answerer, 'new_answer_comment');
});

// ---------------------------------------------------------------------------
// Comment on a profile
// ---------------------------------------------------------------------------

it('notifies the profile owner when someone comments on their profile', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();

    $this->actingAs($commenter)
        ->post(route('profile.comments.store', $owner), [
            'content' => 'Great profile!',
        ])
        ->assertRedirect();

    $notification = assertNotified($owner, 'new_profile_comment');
    expect($notification->data['actor_id'])->toBe($commenter->id);
    expect($notification->data['profile_user_id'])->toBe($owner->id);
});

it('does not notify the profile owner when they comment on their own profile', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)
        ->post(route('profile.comments.store', $owner), [
            'content' => 'Self note.',
        ])
        ->assertRedirect();

    assertNotNotified($owner, 'new_profile_comment');
});

it('notifies the parent comment author when someone replies to a profile comment', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $replier = User::factory()->create();

    $parent = \App\Models\UserComment::create([
        'profile_user_id' => $owner->id,
        'user_id' => $commenter->id,
        'parent_id' => null,
        'content' => 'Top-level profile comment.',
    ]);

    $this->actingAs($replier)
        ->post(route('profile.comments.store', $owner), [
            'content' => 'A reply.',
            'parent_id' => $parent->id,
        ])
        ->assertRedirect();

    $notification = assertNotified($commenter, 'new_profile_comment');
    expect($notification->data['actor_id'])->toBe($replier->id);
    expect($notification->data['parent_id'])->toBe($parent->id);
});

// ---------------------------------------------------------------------------
// Answer accepted
// ---------------------------------------------------------------------------

it('notifies the answer author when their answer is accepted', function () {
    $owner = User::factory()->create();
    $answerer = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $answerer);

    $this->actingAs($owner)
        ->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertRedirect();

    $notification = assertNotified($answerer, 'answer_accepted');
    expect($notification->data['sauce_request_id'])->toBe($sauceRequest->id);
    expect($notification->data['answer_id'])->toBe($answer->id);
});

it('does not notify the answer author when they accept their own answer', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $owner);

    $this->actingAs($owner)
        ->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertRedirect();

    assertNotNotified($owner, 'answer_accepted');
});

it('notifies bookmarkers when a bookmarked request has an accepted answer', function () {
    $owner = User::factory()->create();
    $answerer = User::factory()->create();
    $bookmarker = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $answerer);

    SauceRequestBookmark::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $bookmarker->id,
    ]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertRedirect();

    $notification = assertNotified($bookmarker, 'bookmarked_request_accepted');
    expect($notification->data['sauce_request_id'])->toBe($sauceRequest->id);
});

it('does not notify the accepting user as a bookmarker', function () {
    $owner = User::factory()->create();
    $answerer = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);
    $answer = makeAnswer($sauceRequest, $answerer);

    // The owner bookmarks their own request, then accepts an answer.
    SauceRequestBookmark::create([
        'sauce_request_id' => $sauceRequest->id,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->post(route('sauce-requests.answers.accept', [$sauceRequest, $answer]))
        ->assertRedirect();

    assertNotNotified($owner, 'bookmarked_request_accepted');
});

// ---------------------------------------------------------------------------
// Notifications page
// ---------------------------------------------------------------------------

it('redirects guests away from the notifications page', function () {
    $this->get(route('notifications'))->assertRedirect(route('login'));
});

it('lists the authenticated user notifications', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'Artist is Snale.',
        ])
        ->assertRedirect();

    $this->actingAs($owner)
        ->get(route('notifications'))
        ->assertOk()
        ->assertSee('provided an answer to your sauce request.')
        ->assertSee($member->username);
});

it('marks notifications as read when the page is viewed', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeSauceRequest($owner);

    $this->actingAs($member)
        ->post(route('sauce-requests.answers.store', $sauceRequest), [
            'content' => 'Artist is Snale.',
        ])
        ->assertRedirect();

    expect($owner->unreadNotifications()->count())->toBe(1);

    $this->actingAs($owner)
        ->get(route('notifications'))
        ->assertOk();

    expect($owner->unreadNotifications()->count())->toBe(0);
    expect($owner->readNotifications()->count())->toBe(1);
});