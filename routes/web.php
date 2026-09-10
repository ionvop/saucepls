<?php

use App\Http\Controllers\Auth\EmailLoginController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SauceAnswerController;
use App\Http\Controllers\SauceAnswerCommentController;
use App\Http\Controllers\SauceRequestCommentController;
use App\Http\Controllers\SauceRequestController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SauceRequestTagController;
use App\Http\Controllers\SauceRequestTagHistoryController;
use App\Http\Controllers\SauceRequestTextController;
use App\Http\Controllers\SauceRequestTextHistoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserCommentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Guest auth routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [EmailLoginController::class, 'show'])->name('login');

    Route::post('/login/email', [EmailLoginController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('login.email');

    Route::get('/login/email/verify', [EmailLoginController::class, 'verify'])->name('login.verify');

    Route::post('/login/email/verify', [EmailLoginController::class, 'verifyCode'])
        ->middleware('throttle:5,1')
        ->name('login.verify.submit');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

// --- Authenticated routes ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    // --- Subscription feed ---
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');

    // --- Notifications ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/create', [SauceRequestController::class, 'create'])->name('create');
    Route::post('/sauce-requests/upload', [SauceRequestController::class, 'upload'])
        ->middleware('throttle:upload')
        ->name('sauce-requests.upload');
    Route::get('/sauce-requests/{sauceRequest}/details', [SauceRequestController::class, 'details'])->name('sauce-requests.details');
    Route::get('/sauce-requests/{sauceRequest}/duplicate/{duplicate}', [SauceRequestController::class, 'duplicate'])->name('sauce-requests.duplicate');
    Route::get('/sauce-requests/{sauceRequest}/sauce', [SauceRequestController::class, 'sauce'])->name('sauce-requests.sauce');
    Route::post('/sauce-requests/{sauceRequest}/publish', [SauceRequestController::class, 'publish'])->name('sauce-requests.publish');
    Route::post('/sauce-requests/{sauceRequest}/cancel', [SauceRequestController::class, 'cancel'])->name('sauce-requests.cancel');

    Route::get('/sauce-requests/{sauceRequest}/edit', [SauceRequestController::class, 'edit'])->name('sauce-requests.edit');
    Route::put('/sauce-requests/{sauceRequest}', [SauceRequestController::class, 'update'])->name('sauce-requests.update');
    Route::delete('/sauce-requests/{sauceRequest}', [SauceRequestController::class, 'destroy'])->name('sauce-requests.destroy');

    // --- Bookmarks ---
    Route::post('/sauce-requests/{sauceRequest}/bookmark', [SauceRequestController::class, 'bookmark'])
        ->middleware('throttle:bookmarks')
        ->name('sauce-requests.bookmark');
    Route::delete('/sauce-requests/{sauceRequest}/bookmark', [SauceRequestController::class, 'unbookmark'])
        ->middleware('throttle:bookmarks')
        ->name('sauce-requests.unbookmark');

    // --- Community tagging ---
    Route::put('/sauce-requests/{sauceRequest}/tags', [SauceRequestTagController::class, 'update'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.tags.update');

    // --- Tagging history ---
    Route::get('/sauce-requests/{sauceRequest}/tags/history', [SauceRequestTagHistoryController::class, 'index'])->name('sauce-requests.tags.history');
    Route::post('/sauce-requests/{sauceRequest}/tags/history/{taggingHistory}/restore', [SauceRequestTagHistoryController::class, 'restore'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.tags.history.restore');

    // --- Community extracted-text editing ---
    Route::put('/sauce-requests/{sauceRequest}/text', [SauceRequestTextController::class, 'update'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.text.update');

    // --- Extracted-text history ---
    Route::get('/sauce-requests/{sauceRequest}/text/history', [SauceRequestTextHistoryController::class, 'index'])->name('sauce-requests.text.history');
    Route::post('/sauce-requests/{sauceRequest}/text/history/{textHistory}/restore', [SauceRequestTextHistoryController::class, 'restore'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.text.history.restore');

    // --- Comments ---
    Route::post('/sauce-requests/{sauceRequest}/comments', [SauceRequestCommentController::class, 'store'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.comments.store');
    Route::delete('/sauce-requests/{sauceRequest}/comments/{comment}', [SauceRequestCommentController::class, 'destroy'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.comments.destroy');

    // --- Comment likes ---
    Route::post('/sauce-requests/{sauceRequest}/comments/{comment}/like', [SauceRequestCommentController::class, 'like'])
        ->middleware('throttle:comment_likes')
        ->name('sauce-requests.comments.like');
    Route::delete('/sauce-requests/{sauceRequest}/comments/{comment}/like', [SauceRequestCommentController::class, 'unlike'])
        ->middleware('throttle:comment_likes')
        ->name('sauce-requests.comments.unlike');

    // --- Answers ---
    Route::post('/sauce-requests/{sauceRequest}/answers', [SauceAnswerController::class, 'store'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.answers.store');
    Route::delete('/sauce-requests/{sauceRequest}/answers/{answer}', [SauceAnswerController::class, 'destroy'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.answers.destroy');

    // --- Answer likes ---
    Route::post('/sauce-requests/{sauceRequest}/answers/{answer}/like', [SauceAnswerController::class, 'like'])
        ->middleware('throttle:comment_likes')
        ->name('sauce-requests.answers.like');
    Route::delete('/sauce-requests/{sauceRequest}/answers/{answer}/like', [SauceAnswerController::class, 'unlike'])
        ->middleware('throttle:comment_likes')
        ->name('sauce-requests.answers.unlike');

    // --- Answer comments ---
    Route::post('/sauce-requests/{sauceRequest}/answers/{answer}/comments', [SauceAnswerCommentController::class, 'store'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.answers.comments.store');
    Route::delete('/sauce-requests/{sauceRequest}/answers/{answer}/comments/{comment}', [SauceAnswerCommentController::class, 'destroy'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.answers.comments.destroy');

    // --- Answer comment likes ---
    Route::post('/sauce-requests/{sauceRequest}/answers/{answer}/comments/{comment}/like', [SauceAnswerCommentController::class, 'like'])
        ->middleware('throttle:comment_likes')
        ->name('sauce-requests.answers.comments.like');
    Route::delete('/sauce-requests/{sauceRequest}/answers/{answer}/comments/{comment}/like', [SauceAnswerCommentController::class, 'unlike'])
        ->middleware('throttle:comment_likes')
        ->name('sauce-requests.answers.comments.unlike');

    // --- Accepting answers ---
    Route::post('/sauce-requests/{sauceRequest}/answers/{answer}/accept', [SauceAnswerController::class, 'accept'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.answers.accept');
    Route::delete('/sauce-requests/{sauceRequest}/answers/{answer}/accept', [SauceAnswerController::class, 'unaccept'])
        ->middleware('throttle:community_edits')
        ->name('sauce-requests.answers.unaccept');

    // --- Following ---
    Route::post('/u/{user}/follow', [FollowController::class, 'follow'])
        ->middleware('throttle:follows')
        ->name('profile.follow');
    Route::delete('/u/{user}/follow', [FollowController::class, 'unfollow'])
        ->middleware('throttle:follows')
        ->name('profile.unfollow');

    // --- Profile comments ---
    Route::post('/u/{user}/comments', [UserCommentController::class, 'store'])
        ->middleware('throttle:community_edits')
        ->name('profile.comments.store');
    Route::delete('/u/{user}/comments/{comment}', [UserCommentController::class, 'destroy'])
        ->middleware('throttle:community_edits')
        ->name('profile.comments.destroy');

    // --- Profile comment likes ---
    Route::post('/u/{user}/comments/{comment}/like', [UserCommentController::class, 'like'])
        ->middleware('throttle:comment_likes')
        ->name('profile.comments.like');
    Route::delete('/u/{user}/comments/{comment}/like', [UserCommentController::class, 'unlike'])
        ->middleware('throttle:comment_likes')
        ->name('profile.comments.unlike');
})->scopeBindings();

// --- Public profile routes ---
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
Route::get('/u/{user}/followers', [ProfileController::class, 'followers'])->name('profile.followers');
Route::get('/u/{user}/following', [ProfileController::class, 'following'])->name('profile.following');
Route::get('/u/{user}/accepted-answers', [ProfileController::class, 'acceptedAnswers'])->name('profile.accepted-answers');
Route::get('/u/{user}/requests', [ProfileController::class, 'requests'])->name('profile.requests');
Route::get('/u/{user}/bookmarks', [ProfileController::class, 'bookmarks'])->name('profile.bookmarks');
Route::get('/u/{user}/answers', [ProfileController::class, 'answers'])->name('profile.answers');
Route::get('/u/{user}/comments', [ProfileController::class, 'comments'])->name('profile.comments');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');

// --- Settings (guest-accessible; authed users save via the auth-only PUT route) ---
Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');

// --- Public sauce request routes ---
// The browse feed lives at /search (see SauceRequestController::search).
// The old /sauce-requests index is kept as a redirect so existing links and
// bookmarks keep working. The /sauce-requests/{sauceRequest} show route is
// unaffected.
Route::redirect('/sauce-requests', '/search');
Route::get('/sauce-requests/{sauceRequest}', [SauceRequestController::class, 'show'])->name('sauce-requests.show');

Route::get('/search', [SauceRequestController::class, 'search'])->name('search');

// Tag autocomplete suggestions for the search field.
Route::get('/tags/autocomplete', [TagController::class, 'autocomplete'])
    ->middleware('throttle:60,1')
    ->name('tags.autocomplete');
