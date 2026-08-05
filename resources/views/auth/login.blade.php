@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name', 'SaucePls'))

@section('content')
    <h1 class="text-xl font-bold text-white">Welcome back</h1>
    <p class="mt-1 text-sm text-gray-400">Sign in to SaucePls with your email or Google account.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Email OTP --}}
    <form method="POST" action="{{ route('login.email') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-gray-300">Email address</label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
                class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40"
            >
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-[#5555AA] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6666BB]"
        >
            Send me a login code
        </button>
    </form>

    {{-- Divider --}}
    <div class="my-6 flex items-center gap-3">
        <div class="h-px flex-1 bg-white/10"></div>
        <span class="text-xs uppercase tracking-wide text-gray-500">or</span>
        <div class="h-px flex-1 bg-white/10"></div>
    </div>

    {{-- Google --}}
    <a
        href="{{ route('auth.google.redirect') }}"
        class="flex w-full items-center justify-center gap-2 rounded-lg border border-white/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-100"
    >
        <x-lucide-chrome class="h-5 w-5" />
        Continue with Google
    </a>
@endsection
