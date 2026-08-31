<?php

use App\Models\SauceRequest;
use App\Models\SauceRequestTextHistory;
use App\Models\User;
use App\Services\TextService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a sauce request owned by the given user.
 */
function makeTextSauceRequest(User $user, array $attributes = []): SauceRequest
{
    return SauceRequest::create(array_merge([
        'user_id' => $user->id,
        'title' => 'Original title',
        'description' => 'Original description.',
        'text' => 'capybara ?! capybara !',
        'image_path' => 'sauce-requests/test.png',
        'phash64' => 'aaaaaaaaaaaaaaaa',
        'is_explicit' => true,
        'published_at' => now(),
    ], $attributes));
}

// ---------------------------------------------------------------------------
// Community text editing
// ---------------------------------------------------------------------------

it('lets any authenticated user replace the extracted text', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($member)
        ->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => 'coconut doggy o my gosh',
        ])
        ->assertRedirect();

    expect($sauceRequest->fresh()->text)->toBe('coconut doggy o my gosh');

    $history = SauceRequestTextHistory::latest('id')->firstOrFail();
    expect($history->user_id)->toBe($member->id);
    expect($history->text_snapshot)->toBe('coconut doggy o my gosh');
});

it('lets any authenticated user clear the extracted text', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($member)
        ->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => '',
        ])
        ->assertRedirect();

    expect($sauceRequest->fresh()->text)->toBe('');

    $history = SauceRequestTextHistory::latest('id')->firstOrFail();
    expect($history->user_id)->toBe($member->id);
    expect($history->text_snapshot)->toBe('');
});

it('redirects guests away from the text route', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTextSauceRequest($owner);

    $this->put(route('sauce-requests.text.update', $sauceRequest), ['text' => 'cute'])
        ->assertRedirect(route('login'));
});

it('does not record history when the text is unchanged', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($member)
        ->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => 'capybara ?! capybara !',
        ])
        ->assertRedirect();

    expect(SauceRequestTextHistory::count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Rate limiting community text edits
// ---------------------------------------------------------------------------

it('rate limits a member to 5 text edits per minute', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($member);

    for ($i = 0; $i < 5; $i++) {
        $this->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => "text {$i}",
        ])->assertRedirect();
    }

    $this->put(route('sauce-requests.text.update', $sauceRequest), [
        'text' => 'text 6',
    ])->assertTooManyRequests();
});

it('shares the rate limit budget between tags and text edits', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($member);

    // 3 tag edits + 2 text edits = 5 total, then the 6th is throttled.
    for ($i = 0; $i < 3; $i++) {
        $this->put(route('sauce-requests.tags.update', $sauceRequest), [
            'tags' => "tag{$i}",
        ])->assertRedirect();
    }

    for ($i = 0; $i < 2; $i++) {
        $this->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => "text {$i}",
        ])->assertRedirect();
    }

    $this->put(route('sauce-requests.text.update', $sauceRequest), [
        'text' => 'text 6',
    ])->assertTooManyRequests();
});

it('does not rate limit the owner of the sauce request', function () {
    $owner = User::factory()->create();
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($owner);

    for ($i = 0; $i < 10; $i++) {
        $this->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => "text {$i}",
        ])->assertRedirect();
    }
});

it('does not rate limit moderators', function () {
    $owner = User::factory()->create();
    $moderator = User::factory()->create(['type' => 'moderator']);
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($moderator);

    for ($i = 0; $i < 10; $i++) {
        $this->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => "text {$i}",
        ])->assertRedirect();
    }
});

it('does not rate limit admins', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['type' => 'admin']);
    $sauceRequest = makeTextSauceRequest($owner);

    $this->actingAs($admin);

    for ($i = 0; $i < 10; $i++) {
        $this->put(route('sauce-requests.text.update', $sauceRequest), [
            'text' => "text {$i}",
        ])->assertRedirect();
    }
});