@extends('layouts.app')

@section('title', 'Home - ' . config('app.name', 'SaucePls'))

@section('content')
    {{-- Hero (guests only) --}}
    @guest
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-[#1a1a1a] to-[#111111] px-6 py-12 sm:px-10 sm:py-16">
            <div class="pointer-events-none absolute -right-10 -top-10 h-64 w-64 rounded-full bg-[#5555AA]/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-[#5555AA]/5 blur-2xl"></div>

            <div class="relative">
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Find the sauce behind any image.
                </h1>
                <p class="mt-3 max-w-2xl text-base text-gray-400 sm:text-lg">
                    Post an image and let the community track down its source. Browse
                    popular, trending, and recent requests, or search by keyword and tag.
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="{{ route('create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#5555AA] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6666BB]">
                        <x-lucide-plus class="h-4 w-4" />
                        Post a request
                    </a>
                    <a href="{{ route('search') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-5 py-2.5 text-sm font-semibold text-gray-200 transition hover:border-white/20 hover:text-white">
                        <x-lucide-search class="h-4 w-4" />
                        Browse requests
                    </a>
                </div>
            </div>
        </div>
    @endguest

    {{-- Subscription feed (authenticated users only) --}}
    @auth
        <x-home-section
            title="Subscription feed"
            :viewAllHref="route('subscriptions')"
            :sauceRequests="$subscriptionFeed"
            emptyMessage="Follow users to see their posts here."
        />
    @endauth

    {{-- Popular this month --}}
    <x-home-section
        title="Popular this month"
        :viewAllHref="route('search', ['q' => 'within:1m', 'sort' => 'popular'])"
        :sauceRequests="$popularThisMonth"
        emptyMessage="No popular requests this month yet."
    />

    {{-- Trending --}}
    <x-home-section
        title="Trending"
        :viewAllHref="route('search', ['sort' => 'trending'])"
        :sauceRequests="$trending"
        emptyMessage="Nothing trending right now."
    />

    {{-- Recent --}}
    <x-home-section
        title="Recent"
        :viewAllHref="route('search')"
        :sauceRequests="$recent"
        emptyMessage="No requests posted yet."
    />
@endsection
