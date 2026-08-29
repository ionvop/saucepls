@extends('layouts.app')

@section('title', 'We found a match - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-2xl" x-data="leavePrompt">
        <a href="{{ route('create') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
            <x-lucide-arrow-left class="h-4 w-4" />
            Back to upload
        </a>

        <div class="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-500/20 text-green-300">
                    <x-lucide-search class="h-5 w-5" />
                </span>
                <div>
                    <h1 class="text-xl font-bold text-white">We found a match for your image</h1>
                    <p class="mt-1 text-sm text-gray-400">
                        SauceNAO identified your image with high confidence. You can view the result to see if it
                        already has the source you're looking for, or continue posting your own request.
                    </p>
                </div>
            </div>

            @if (empty($matches))
                <div class="mt-6 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-300">
                    We couldn't load the match details right now. You can continue posting your request anyway.
                </div>
            @else
                @php($match = $matches[0])

                {{-- Top match --}}
                <div class="mt-6 overflow-hidden rounded-xl border border-white/10 bg-[#111111]">
                    <div class="bg-[#1a1a1a]">
                        @if ($match['thumbnail'])
                            <img src="{{ $match['thumbnail'] }}" alt="{{ $match['title'] ?? 'Sauce match' }}"
                                class="mx-auto max-h-48 w-full object-contain">
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-semibold text-green-300">
                                <x-lucide-percent class="h-3.5 w-3.5" />
                                {{ number_format($match['similarity'], 1) }}% match
                            </span>
                            @if ($match['index_name'])
                                <span class="rounded-full bg-[#5555AA]/20 px-2.5 py-0.5 text-xs font-semibold text-[#8888CC]">
                                    {{ $match['index_name'] }}
                                </span>
                            @endif
                        </div>
                        @if ($match['title'])
                            <h2 class="mt-2 font-semibold text-white">{{ $match['title'] }}</h2>
                        @endif
                        @if ($match['author'])
                            <p class="mt-1 text-sm text-gray-400">by {{ $match['author'] }}</p>
                        @endif
                        @if (! empty($match['urls']))
                            <a href="{{ $match['urls'][0] }}" target="_blank" rel="noopener"
                                class="mt-3 inline-flex items-center gap-1.5 text-sm text-[#8888CC] transition hover:text-white">
                                <x-lucide-external-link class="h-4 w-4" />
                                View source
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                @if (! empty($matches) && ! empty($matches[0]['urls']))
                    <a href="{{ $matches[0]['urls'][0] }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                        <x-lucide-eye class="h-4 w-4" />
                        View result
                    </a>
                @endif
                <form method="POST" action="{{ route('sauce-requests.cancel', $sauceRequest) }}">
                    @csrf
                    <button type="submit" @click="allowLeave()"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                        <x-lucide-x class="h-4 w-4" />
                        Cancel
                    </button>
                </form>
                <a href="{{ route('sauce-requests.details', $sauceRequest) }}" @click="allowLeave()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#6666BB]">
                    Continue anyway
                    <x-lucide-arrow-right class="h-4 w-4" />
                </a>
            </div>
        </div>
    </div>
@endsection