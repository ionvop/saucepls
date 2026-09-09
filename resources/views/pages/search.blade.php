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
        <form
            method="GET"
            action="{{ route('search') }}"
            class="mt-6 space-y-4"
            x-data="searchTimezone"
        >
            {{-- Browser timezone, sent so date prefixes (since:/until:) can be
                 converted from the visitor's timezone to UTC. Falls back to UTC
                 on the server when JavaScript is disabled. --}}
            <input type="hidden" name="tz" x-ref="tz">

            {{-- Keyword with tag autocomplete --}}
            <div
                class="relative"
                x-data="tagSuggestions({ endpoint: @js(route('tags.autocomplete')) })"
            >
                <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-gray-500" />
                <input
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Search sauce requests..."
                    x-model="value"
                    x-ref="input"
                    @input="open = true"
                    @keydown.arrow-down.prevent="moveHighlight(1)"
                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                    @keydown.enter="open && suggestions.length ? (select(highlightIndex), $event.preventDefault()) : null"
                    @keydown.tab="open ? (select(highlightIndex), $event.preventDefault()) : null"
                    @keydown.escape="close()"
                    @click.outside="close()"
                    class="w-full rounded-lg border border-white/10 bg-white/[0.03] py-2 pl-10 pr-3 text-sm text-white placeholder-gray-500 focus:border-[#5555AA]/60 focus:outline-none focus:ring-1 focus:ring-[#5555AA]/40"
                >

                {{-- Suggestion dropdown --}}
                <div
                    x-show="open && suggestions.length"
                    x-transition.opacity.duration.150ms
                    x-cloak
                    role="listbox"
                    aria-label="Tag suggestions"
                    class="absolute inset-x-0 top-full z-40 mt-1 overflow-hidden rounded-lg border border-white/10 bg-[#1a1a1a] py-1 shadow-xl shadow-black/40"
                >
                    <template x-for="(tag, index) in suggestions" :key="tag.id">
                        <button
                            type="button"
                            role="option"
                            :aria-selected="highlightIndex === index"
                            @mouseenter="highlightIndex = index"
                            @mousedown.prevent="select(index)"
                            class="flex w-full items-center justify-between gap-3 px-3 py-1.5 text-left text-sm transition"
                            :class="highlightIndex === index
                                ? 'bg-[#5555AA]/20 font-medium text-white'
                                : 'text-gray-200 hover:bg-white/5 hover:text-white'"
                        >
                            <span x-text="tag.name"></span>
                            <span class="shrink-0 text-xs text-gray-500" x-text="`${tag.usage_count} request${tag.usage_count === 1 ? '' : 's'}`"></span>
                        </button>
                    </template>
                </div>
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
            Tip: use <code class="text-gray-400">tag:1girl</code> to match a tag only, <code class="text-gray-400">text:"coconut doggy"</code> for an exact phrase, <code class="text-gray-400">-kitty</code> to exclude a word, and <code class="text-gray-400">since:2026-04-20</code>, <code class="text-gray-400">until:2026-09-11</code>, or <code class="text-gray-400">within:5d</code> (hours, days, weeks, months, or years) to filter by published date.
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
