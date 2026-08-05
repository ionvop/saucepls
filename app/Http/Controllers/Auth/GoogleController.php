<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect the user to Google's consent screen.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign-in failed. Please try again.']);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors(['email' => 'Google did not provide an email address.']);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'username' => $this->uniqueUsername($googleUser->getName() ?? $email),
                'email' => $email,
            ]);
        }

        Auth::login($user);
        session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Derive a unique username from the Google profile name.
     */
    protected function uniqueUsername(string $name): string
    {
        $base = Str::slug($name, '_', 'en') ?: 'user';
        $base = Str::limit($base, 30, '');

        $username = $base;
        $suffix = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = Str::limit($base, 30 - strlen((string) $suffix) - 1, '') . '_' . $suffix;
            $suffix++;
        }

        return $username;
    }
}
