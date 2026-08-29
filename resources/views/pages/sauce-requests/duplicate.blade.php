@extends('layouts.app')

@section('title', 'Possible duplicate - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('create') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
            <x-lucide-arrow-left class="h-4 w-4" />
            Back to upload
        </a>

        <div class="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-amber-300">
                    <x-lucide-copy class="h-5 w-5" />
                </span>
                <div>
                    <h1 class="text-xl font-bold text-white">This image may already have a request</h1>
                    <p class="mt-1 text-sm text-gray-400">
                        We found an existing sauce request that looks very similar to the image you uploaded.
                        You can view it to see if it already has an answer, or continue posting your own request.
                    </p>
                </div>
            </div>

            {{-- Existing request --}}
            <a href="{{ route('sauce-requests.show', $duplicate) }}"
                class="mt-6 block overflow-hidden rounded-xl border border-white/10 bg-[#111111] transition hover:border-[#5555AA]/50">
                <div class="bg-[#1a1a1a]">
                    @if ($duplicate->image_url)
                        <img src="{{ $duplicate->image_url }}" alt="{{ $duplicate->title }}"
                            class="mx-auto max-h-48 w-full object-contain">
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($duplicate->isAccepted())
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-semibold text-green-300">
                                <x-lucide-check class="h-3.5 w-3.5" />
                                Solved
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#5555AA]/20 px-2.5 py-0.5 text-xs font-semibold text-[#8888CC]">
                                <x-lucide-hourglass class="h-3.5 w-3.5" />
                                Unsolved
                            </span>
                        @endif
                        @if ($duplicate->is_explicit)
                            <span class="rounded-full bg-red-500/20 px-2.5 py-0.5 text-xs font-semibold text-red-300">
                                NSFW
                            </span>
                        @endif
                    </div>
                    <h2 class="mt-2 font-semibold text-white">{{ $duplicate->title }}</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Posted by {{ $duplicate->user?->username ?? 'Unknown' }}
                        <span>·</span>
                        {{ $duplicate->created_at?->format('M j, Y') }}
                    </p>
                </div>
            </a>

            {{-- Actions --}}
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('sauce-requests.show', $duplicate) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                    <x-lucide-eye class="h-4 w-4" />
                    View existing request
                </a>
                <a href="{{ route('sauce-requests.details', $sauceRequest) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#6666BB]">
                    Continue anyway
                    <x-lucide-arrow-right class="h-4 w-4" />
                </a>
            </div>
        </div>
    </div>
@endsection