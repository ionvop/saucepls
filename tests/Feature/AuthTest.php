<?php

use App\Models\User;
use App\Services\BrevoMailService;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Email OTP flow
// ---------------------------------------------------------------------------

it('shows the login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Send me a login code')
        ->assertSee('Continue with Google');
});

it('sends a code to the email and stores it', function () {
    $otp = Mockery::mock(OtpService::class);
    $otp->shouldReceive('generate')->once()->with('user@example.com')->andReturn('123456');

    $mail = Mockery::mock(BrevoMailService::class);
    $mail->shouldReceive('send')->once()->andReturn(true);

    $this->app->instance(OtpService::class, $otp);
    $this->app->instance(BrevoMailService::class, $mail);

    $this->post(route('login.email'), ['email' => 'user@example.com'])
        ->assertRedirect(route('login.verify'));

    $this->assertTrue(session()->has('auth.email'));
    $this->assertEquals('user@example.com', session('auth.email'));
});

it('rejects an invalid email when sending a code', function () {
    $this->post(route('login.email'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});

it('logs in an existing user with a valid code', function () {
    $user = User::factory()->create(['email' => 'existing@example.com']);

    $otp = Mockery::mock(OtpService::class);
    $otp->shouldReceive('verify')->once()->with('existing@example.com', '123456')->andReturn(true);

    $this->app->instance(OtpService::class, $otp);

    session(['auth.email' => 'existing@example.com']);

    $this->post(route('login.verify.submit'), ['code' => '123456'])
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('redirects a new user to registration after a valid code', function () {
    $otp = Mockery::mock(OtpService::class);
    $otp->shouldReceive('verify')->once()->with('new@example.com', '123456')->andReturn(true);

    $this->app->instance(OtpService::class, $otp);

    session(['auth.email' => 'new@example.com']);

    $this->post(route('login.verify.submit'), ['code' => '123456'])
        ->assertRedirect(route('register'));

    $this->assertTrue(session()->has('auth.verified_email'));
    $this->assertFalse(session()->has('auth.email'));
});

it('rejects an invalid code', function () {
    $otp = Mockery::mock(OtpService::class);
    $otp->shouldReceive('verify')->once()->andReturn(false);

    $this->app->instance(OtpService::class, $otp);

    session(['auth.email' => 'user@example.com']);

    $this->post(route('login.verify.submit'), ['code' => '000000'])
        ->assertSessionHasErrors('code');
});

it('registers a new user with a chosen username', function () {
    session(['auth.verified_email' => 'new@example.com']);

    $this->post(route('register.store'), ['username' => 'sauce_hunter'])
        ->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', [
        'email' => 'new@example.com',
        'username' => 'sauce_hunter',
    ]);

    $this->assertAuthenticated();
});

it('rejects a duplicate username during registration', function () {
    User::factory()->create(['username' => 'taken']);

    session(['auth.verified_email' => 'new@example.com']);

    $this->post(route('register.store'), ['username' => 'taken'])
        ->assertSessionHasErrors('username');
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

// ---------------------------------------------------------------------------
// Google OAuth flow
// ---------------------------------------------------------------------------

it('redirects to Google', function () {
    Socialite::shouldReceive('driver->redirect')
        ->once()
        ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    $this->get(route('auth.google.redirect'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

it('logs in an existing user via Google callback', function () {
    $user = User::factory()->create(['email' => 'google@example.com']);

    $googleUser = new SocialiteUser();
    $googleUser->map(['email' => 'google@example.com', 'name' => 'Google User']);

    Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('creates a new user via Google callback', function () {
    $googleUser = new SocialiteUser();
    $googleUser->map(['email' => 'brandnew@example.com', 'name' => 'Jane Doe']);

    Socialite::shouldReceive('driver->user')->once()->andReturn($googleUser);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('home'));

    $this->assertDatabaseHas('users', ['email' => 'brandnew@example.com']);
    $this->assertAuthenticated();
});

it('redirects back to login when Google fails', function () {
    Socialite::shouldReceive('driver->user')->once()->andThrow(new \Exception('boom'));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
});
