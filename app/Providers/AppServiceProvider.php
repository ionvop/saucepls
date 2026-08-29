<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Models\SauceRequest;

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
        $this->configureTaggingRateLimiter();
    }

    /**
     * Rate limit community tag modifications to prevent tag-wars.
     *
     * Owners of the sauce request and staff (moderators/admins) are exempt.
     * Everyone else shares a single per-user budget across all requests.
     */
    protected function configureTaggingRateLimiter(): void
    {
        RateLimiter::for('tagging', function (Request $request) {
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
