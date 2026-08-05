@extends('layouts.auth')

@section('title', 'Create your account - ' . config('app.name', 'SaucePls'))

@section('content')
    <h1 class="text-xl font-bold text-white">Almost there</h1>
    <p class="mt-1 text-sm text-gray-400">
        Your email <span class="font-medium text-gray-200">{{ $email }}</span> is verified.
        Pick a username to finish creating your account.
    </p>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="username" class="mb-1 block text-sm font-medium text-gray-300">Username</label>
            <input
                type="text"
                name="username"
                id="username"
                value="{{ old('username') }}"
                required
                minlength="3"
                maxlength="30"
                autofocus
                autocomplete="username"
                placeholder="sauce_hunter"
                class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40"
            >
            <p class="mt-1 text-xs text-gray-500">Letters, numbers, hyphens, and underscores only.</p>
        </div>

        <button
            type="submit"
            class="w-full rounded-lg bg-[#5555AA] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6666BB]"
        >
            Create account
        </button>
    </form>
@endsection
