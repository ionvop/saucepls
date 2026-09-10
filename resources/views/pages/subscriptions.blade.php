@extends('layouts.app')

@section('title', 'Subscription feed - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="grid gap-6 lg:grid-cols-[260px_1fr]">
            {{-- Left panel: followed users --}}
            <aside class="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                <h2 class="px-3 pb-2 text-sm font-semibold uppercase tracking-wide text-gray-400">
                    Following
                    @if ($following->isNotEmpty())
                        <span class="text-gray-500">({{ $following->count() }})</span>
                    @endif
                </h2>

                @if ($following->isEmpty())
                    <p class="px-3 py-6 text-sm text-gray-500">
                        You aren't following anyone yet.
                    </p>
                @else
                    <div class="divide-y divide-white/10">
                        @foreach ($following as $followedUser)
                            <a href="{{ route('profile.show', $followedUser->username) }}"
                                class="flex items-center gap-3 px-3 py-2.5 transition hover:bg-white/5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#5555AA]/20 text-sm font-bold text-[#8888CC]">
                                    @if ($followedUser->avatar_url)
                                        <img src="{{ $followedUser->avatar_url }}" alt="{{ $followedUser->username }}" class="h-full w-full object-cover">
                                    @else
                                        {{ strtoupper(substr($followedUser->username, 0, 1)) }}
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-white">{{ $followedUser->username }}</span>
                                    <span class="block text-xs text-gray-400">{{ $followedUser->followers_count }} follower{{ $followedUser->followers_count === 1 ? '' : 's' }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </aside>

            {{-- Main feed --}}
            <section>
                <div class="flex items-center justify-between gap-4">
                    <h1 class="text-xl font-bold text-white">Subscription feed</h1>
                </div>

                @if ($sauceRequests->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                        <x-lucide-bell class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                        <p>Follow users to see their posts here.</p>
                    </div>
                @else
                    <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($sauceRequests as $sauceRequest)
                            <x-sauce-request-card :sauceRequest="$sauceRequest" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $sauceRequests->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection