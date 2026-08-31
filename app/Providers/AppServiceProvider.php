<?php

namespace App\Providers;

use App\Models\SauceRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommunityEditsRateLimiter();
        $this->configureCommentLikesRateLimiter();
        $this->configureUploadRateLimiter();
    }

    /**
     * Rate limit comment likes to prevent a user from spamming the like and
     * unlike buttons.
     *
     * Staff (moderators/admins) are exempt.
     */
    protected function configureCommentLikesRateLimiter(): void
    {
        RateLimiter::for('comment_likes', function (Request $request) {
            $user = $request->user();

            if ($user && $user->isStaff()) {
                return Limit::none();
            }

            return Limit::perMinute(20)->by($user?->id ?? $request->ip());
        });
    }

    /**
     * Rate limit sauce request uploads to prevent abuse of the pre-post
     * pipeline (perceptual hashing, SauceNAO, OCR, and tag inference),
     * which all run within the upload request.
     *
     * Staff (moderators/admins) are exempt.
     */
    protected function configureUploadRateLimiter(): void
    {
        RateLimiter::for('upload', function (Request $request) {
            $user = $request->user();

            if ($user && $user->isStaff()) {
                return Limit::none();
            }

            return Limit::perMinute(3)->by($user?->id ?? $request->ip());
        });
    }

    /**
     * Rate limit community tag and extracted-text modifications to prevent
     * tag-wars and text-wars.
     *
     * Owners of the sauce request and staff (moderators/admins) are exempt.
     * Everyone else shares a single per-user budget across all requests.
     */
    protected function configureCommunityEditsRateLimiter(): void
    {
        RateLimiter::for('community_edits', function (Request $request) {
            $user = $request->user();

            $sauceRequestId = $request->route('sauceRequest');
            $isOwner = $sauceRequestId !== null
                && $user !== null
                && SauceRequest::whereKey($sauceRequestId)->value('user_id') === $user->id;

            if ($user && ($user->isStaff() || $isOwner)) {
                return Limit::none();
            }

            return Limit::perMinute(5)->by($user?->id ?? $request->ip());
        });
    }
}
