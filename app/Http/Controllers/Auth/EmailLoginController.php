<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendCodeRequest;
use App\Services\BrevoMailService;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailLoginController extends Controller
{
    public function __construct(
        protected OtpService $otp,
        protected BrevoMailService $mail,
    ) {
    }

    /**
     * Show the login page (email + Google options).
     */
    public function show(): View
    {
        return view('auth.login');
    }

    /**
     * Send a one-time code to the given email.
     */
    public function send(SendCodeRequest $request): RedirectResponse
    {
        $email = $request->validated('email');

        $code = $this->otp->generate($email);

        $sent = $this->mail->send(
            $email,
            'Your SaucePls login code',
            view('emails.otp', ['code' => $code])->render(),
            "Your SaucePls login code is: {$code}"
        );

        if (! $sent) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'We could not send your code right now. Please try again.']);
        }

        $request->session()->put('auth.email', $email);

        return redirect()->route('login.verify')->with('status', 'We sent a code to your email.');
    }

    /**
     * Show the code verification page.
     */
    public function verify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth.email')) {
            return redirect()->route('login');
        }

        return view('auth.verify', [
            'email' => $request->session()->get('auth.email'),
        ]);
    }

    /**
     * Verify the submitted code and log the user in (or start registration).
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $email = $request->session()->get('auth.email');

        if (! $email || ! $this->otp->verify($email, $request->input('code'))) {
            return back()->withErrors(['code' => 'That code is invalid or has expired.']);
        }

        $user = \App\Models\User::query()->where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->forget('auth.email');

            return redirect()->intended(route('home'));
        }

        // New user: remember the verified email and ask for a username.
        $request->session()->put('auth.verified_email', $email);
        $request->session()->forget('auth.email');

        return redirect()->route('register');
    }
}
