@extends('layouts.app')

@section('title', $user->username . ' - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        {{-- Profile header --}}
        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center">
                {{-- Avatar --}}
                <div class="relative shrink-0">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}'s avatar"
                            class="h-24 w-24 rounded-full border-2 border-[#5555AA]/40 object-cover">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-[#5555AA]/40 bg-[#5555AA]/20 text-3xl font-bold text-[#8888CC]">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Online status --}}
                    @if ($user->last_seen_at && $user->last_seen_at->gt(now()->subMinutes(5)))
                        <span class="absolute bottom-1 right-1 h-5 w-5 rounded-full border-4 border-[#111111] bg-green-500" title="Online"></span>
                    @endif
                </div>

                {{-- Identity --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold text-white">{{ $user->username }}</h1>

                        @if ($user->type !== 'member')
                            <span class="rounded-full bg-[#5555AA]/20 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-[#8888CC]">
                                {{ $user->type }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-sm text-gray-400">
                        Joined <span data-time="{{ $user->created_at?->toIso8601String() }}" data-format="month-year">{{ $user->created_at?->format('F Y') }}</span>
                    </p>
                </div>

                {{-- Actions --}}
                <div class="shrink-0">
                    @if ($isOwner)
                        <a href="{{ route('profile.edit') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                            <x-lucide-pencil class="h-4 w-4" />
                            Edit profile
                        </a>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm text-gray-400">
                            <x-lucide-user class="h-4 w-4" />
                            Follow
                        </span>
                    @endif
                </div>
            </div>

            {{-- Bio --}}
            <div class="prose prose-invert mt-6 max-w-none border-t border-white/10 pt-6 text-gray-300">
                {!! $bioHtml !!}
            </div>
        </div>

        {{-- Placeholder sections (posts, sauces, comments not implemented yet) --}}
        <div class="mt-6 grid gap-6 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 text-center">
                <p class="text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-sm text-gray-400">Sauce requests</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 text-center">
                <p class="text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-sm text-gray-400">Sauce answers</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 text-center">
                <p class="text-2xl font-bold text-white">0</p>
                <p class="mt-1 text-sm text-gray-400">Followers</p>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
            Posts, sauces, and comments will appear here once they're implemented.
        </div>
    </div>
@endsection
