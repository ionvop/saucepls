@extends('layouts.auth')

@section('title', 'Verify your email - ' . config('app.name', 'SaucePls'))

@section('content')
    <h1 class="text-xl font-bold text-white">Check your email</h1>
    <p class="mt-1 text-sm text-gray-400">
        We sent a 6-digit code to <span class="font-medium text-gray-200">{{ $email }}</span>.
    </p>

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

    <form method="POST" action="{{ route('login.verify.submit') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="code" class="mb-1 block text-sm font-medium text-gray-300">Login code</label>
            <input
                type="text"
                name="code"
                id="code"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                required
                autofocus
                autocomplete="one-time-code"
                placeholder="123456"
                class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-center text-2xl font-bold tracking-[0.5em] text-white placeholder-gray-600 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40"
            >
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-[#5555AA] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6666BB]"
        >
            Verify &amp; sign in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-400">
        Didn't get a code?
        <a href="{{ route('login') }}" class="font-medium text-[#6666BB] hover:text-[#7777CC]">Try again</a>
    </p>
@endsection
