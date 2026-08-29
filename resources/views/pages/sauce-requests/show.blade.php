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

                {{-- Tags --}}
                <div class="mt-6 border-t border-white/10 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Tags</h2>

                    @if ($sauceRequest->tags->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($sauceRequest->tags as $tag)
                                <span class="inline-flex items-center gap-1 rounded-full bg-[#5555AA]/20 px-3 py-1 text-xs font-medium text-[#8888CC]">
                                    <a href="{{ route('search', ['q' => 'tag:' . $tag->name]) }}" class="hover:text-white">
                                        {{ $tag->name }}
                                    </a>
                                    @auth
                                        <form method="POST" action="{{ route('sauce-requests.tags.destroy', [$sauceRequest, $tag]) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Remove tag"
                                                class="text-[#8888CC]/60 transition hover:text-red-400">
                                                <x-lucide-x class="h-3 w-3" />
                                            </button>
                                        </form>
                                    @endauth
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm text-gray-500">No tags yet.</p>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('sauce-requests.tags.store', $sauceRequest) }}" class="mt-4 flex items-center gap-2">
                            @csrf
                            <input type="text" name="tags" placeholder="Add tags (space-separated)" maxlength="1000"
                                class="w-full max-w-xs rounded-lg border border-white/10 bg-[#111111] px-3 py-1.5 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">
                            <button type="submit"
                                class="rounded-lg bg-[#5555AA] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                Add
                            </button>
                        </form>
                    @endauth
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