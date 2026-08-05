<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the username selection step for a newly verified email.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth.verified_email')) {
            return redirect()->route('login');
        }

        return view('auth.register', [
            'email' => $request->session()->get('auth.verified_email'),
        ]);
    }

    /**
     * Create the account and log the user in.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $email = $request->session()->get('auth.verified_email');

        if (! $email) {
            return redirect()->route('login');
        }

        $user = User::create([
            'username' => $request->validated('username'),
            'email' => $email,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('auth.verified_email');

        return redirect()->intended(route('home'));
    }
}
