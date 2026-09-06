@extends('layouts.app')

@section('title', 'Search - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-white">Search</h1>
                <p class="mt-1 text-sm text-gray-400">Find sauce requests by keyword, tag, or quick text.</p>
            </div>
        </div>

        {{-- Search controls --}}
        <form method="GET" action="{{ route('search') }}" class="mt-6 space-y-4">
            {{-- Keyword --}}
            <div class="relative">
                <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                <input type="search" name="q" value="{{ $q }}" placeholder="Search sauce requests..."
                    class="w-full rounded-lg border border-white/10 bg-white/[0.03] py-2 pl-10 pr-3 text-sm text-white placeholder-gray-500 focus:border-[#5555AA]/60 focus:outline-none focus:ring-1 focus:ring-[#5555AA]/40">
            </div>

            {{-- Filter & sort --}}
            <div class="flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-gray-400">
                    <x-lucide-filter class="h-4 w-4" />
                    Status
                    <x-select name="filter" :value="$filter" :options="['all' => 'All', 'solved' => 'Solved', 'unsolved' => 'Unsolved']" />
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-gray-400">
                    <x-lucide-arrow-down-up class="h-4 w-4" />
                    Sort
                    <x-select name="sort" :value="$sort" :options="['recent' => 'Recent', 'popular' => 'Popular', 'trending' => 'Trending']" />
                </label>

                <button type="submit"
                    class="ml-auto rounded-lg bg-[#5555AA] px-4 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                    Search
                </button>
            </div>
        </form>

        {{-- Search hint --}}
        <p class="mt-3 text-xs text-gray-500">
            Tip: use <code class="text-gray-400">tag:1girl</code> to match a tag only, <code class="text-gray-400">text:"coconut doggy"</code> for an exact phrase, and <code class="text-gray-400">-kitty</code> to exclude a word.
        </p>

        {{-- Results --}}
        @if ($sauceRequests->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                <x-lucide-search class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                <p>No sauce requests match your search.</p>
                <p class="mt-1 text-xs text-gray-600">Try a different keyword, tag, or clear the filters.</p>
            </div>
        @else
            <p class="mt-6 text-xs text-gray-500">
                Showing {{ $sauceRequests->firstItem() ?? 0 }}–{{ $sauceRequests->lastItem() ?? 0 }} of {{ $sauceRequests->total() }} result{{ $sauceRequests->total() === 1 ? '' : 's' }}
            </p>

            <div class="mt-3 grid gap-6 sm:grid-cols-2">
                @foreach ($sauceRequests as $sauceRequest)
                    <x-sauce-request-card :sauceRequest="$sauceRequest" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $sauceRequests->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
