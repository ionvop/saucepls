@extends('layouts.app')

@section('title', $sauceRequest->title . ' - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('sauce-requests.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
            <x-lucide-arrow-left class="h-4 w-4" />
            Back to sauce requests
        </a>

        <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
            {{-- Image --}}
            <div class="bg-[#1a1a1a]">
                @if ($sauceRequest->image_url)
                    <img src="{{ $sauceRequest->image_url }}" alt="{{ $sauceRequest->title }}"
                        class="mx-auto max-h-[70vh] w-full object-contain">
                @endif
            </div>

            {{-- Body --}}
            <div class="p-6 sm:p-8">
                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-2">
                    @if ($sauceRequest->isAccepted())
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

                    @if ($sauceRequest->is_explicit)
                        <span class="rounded-full bg-red-500/20 px-2.5 py-0.5 text-xs font-semibold text-red-300">
                            NSFW
                        </span>
                    @endif

                    @if ($isOwner)
                        <a href="{{ route('sauce-requests.edit', $sauceRequest) }}"
                            class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                            <x-lucide-pencil class="h-3.5 w-3.5" />
                            Edit
                        </a>
                    @endif
                </div>

                {{-- Title --}}
                <h1 class="mt-4 text-2xl font-bold text-white">{{ $sauceRequest->title }}</h1>

                {{-- Author --}}
                <div class="mt-3 flex items-center gap-2 text-sm text-gray-400">
                    @if ($sauceRequest->user?->avatar_url)
                        <img src="{{ $sauceRequest->user->avatar_url }}" alt="{{ $sauceRequest->user->username }}"
                            class="h-6 w-6 rounded-full object-cover">
                    @else
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#5555AA]/20 text-xs font-bold text-[#8888CC]">
                            {{ strtoupper(substr($sauceRequest->user?->username ?? '?', 0, 1)) }}
                        </span>
                    @endif
                    <a href="{{ route('profile.show', $sauceRequest->user?->username ?? '') }}"
                        class="font-medium text-gray-200 hover:text-white">
                        {{ $sauceRequest->user?->username ?? 'Unknown' }}
                    </a>
                    <span>·</span>
                    <span>{{ $sauceRequest->created_at?->format('M j, Y') }}</span>
                </div>

                {{-- Description --}}
                @if ($sauceRequest->description)
                    <div class="mt-6 border-t border-white/10 pt-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Description</h2>
                        <p class="mt-2 whitespace-pre-line text-gray-300">{{ $sauceRequest->description }}</p>
                    </div>
                @endif

                {{-- OCR text --}}
                @if ($sauceRequest->text)
                    <div class="mt-6 border-t border-white/10 pt-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Text in image</h2>
                        <p class="mt-2 whitespace-pre-line text-gray-300">{{ $sauceRequest->text }}</p>
                    </div>
                @endif

                {{-- Tags placeholder --}}
                <div class="mt-6 border-t border-white/10 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Tags</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Tagging is not available yet.
                    </p>
                </div>

                {{-- Answers placeholder --}}
                <div class="mt-6 border-t border-white/10 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Answers</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        No answers yet. Answers will be available soon.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection