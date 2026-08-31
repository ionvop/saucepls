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

                    @if ($isOwner || $isStaff)
                        <div class="ml-auto flex items-center gap-2" x-data>
                            @if ($isOwner)
                                <a href="{{ route('sauce-requests.edit', $sauceRequest) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                                    <x-lucide-pencil class="h-3.5 w-3.5" />
                                    Edit
                                </a>
                            @endif

                            <form x-ref="deleteForm" method="POST" action="{{ route('sauce-requests.destroy', $sauceRequest) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>

                            <button type="button"
                                @click="$dispatch('open-confirm', {
                                    message: 'Delete this sauce request? This cannot be undone.',
                                    action: () => $refs.deleteForm.submit(),
                                })"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-300 transition hover:border-red-500/60 hover:bg-red-500/10 hover:text-red-200">
                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                Delete
                            </button>
                        </div>
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

                    @auth
                        <div x-data="{ editing: false }">
                            {{-- Display mode: plain tag pills + edit button --}}
                            <div x-show="!editing" x-cloak>
                                @if ($sauceRequest->tags->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        @foreach ($sauceRequest->tags as $tag)
                                            <span class="inline-flex items-center rounded-full bg-[#5555AA]/20 px-3 py-1 text-xs font-medium text-[#8888CC]">
                                                <a href="{{ route('search', ['q' => 'tag:' . $tag->name]) }}" class="hover:text-white">
                                                    {{ $tag->name }}
                                                </a>
                                            </span>
                                        @endforeach

                                        <button type="button" @click="editing = true"
                                            class="ml-1 inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-2.5 py-1 text-xs font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                                            <x-lucide-pencil class="h-3.5 w-3.5" />
                                            Edit tags
                                        </button>
                                    </div>
                                @else
                                    <div class="mt-3 flex items-center gap-2">
                                        <p class="text-sm text-gray-500">No tags yet.</p>

                                        <button type="button" @click="editing = true"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-2.5 py-1 text-xs font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                                            <x-lucide-pencil class="h-3.5 w-3.5" />
                                            Edit tags
                                        </button>
                                    </div>
                                @endif

                                <a href="{{ route('sauce-requests.tags.history', $sauceRequest) }}"
                                    class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-gray-400 transition hover:text-white">
                                    <x-lucide-history class="h-3.5 w-3.5" />
                                    View tagging history
                                </a>
                            </div>

                            {{-- Edit mode: single editable field replacing the whole set --}}
                            <form x-show="editing" x-cloak
                                method="POST" action="{{ route('sauce-requests.tags.update', $sauceRequest) }}"
                                class="mt-3 flex flex-col gap-2">
                                @csrf
                                @method('PUT')

                                <input type="text" name="tags" value="{{ old('tags', $sauceRequest->tags->pluck('name')->implode(' ')) }}"
                                    placeholder="Space-separated tags (e.g. 1girl black_hair smile)" maxlength="1000"
                                    class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">
                                <p class="text-xs text-gray-500">
                                    Space-separated, lowercase, alphanumeric, hyphens, and underscores only.
                                </p>

                                <div class="flex items-center gap-2">
                                    <button type="submit"
                                        class="rounded-lg bg-[#5555AA] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                        Save
                                    </button>
                                    <button type="button" @click="editing = false"
                                        class="rounded-lg border border-white/10 px-3 py-1.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                                        Cancel
                                    </button>
                                </div>

                                @error('tags')
                                    <p class="text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    @else
                        {{-- Guest: read-only tag pills --}}
                        @if ($sauceRequest->tags->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($sauceRequest->tags as $tag)
                                    <span class="inline-flex items-center rounded-full bg-[#5555AA]/20 px-3 py-1 text-xs font-medium text-[#8888CC]">
                                        <a href="{{ route('search', ['q' => 'tag:' . $tag->name]) }}" class="hover:text-white">
                                            {{ $tag->name }}
                                        </a>
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-2 text-sm text-gray-500">No tags yet.</p>
                        @endif
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

    {{-- Delete confirmation modal --}}
    <div
        x-data="confirmDialog"
        x-show="open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        @keydown.escape.window="cancel()"
        @open-confirm.window="ask($event.detail.message, $event.detail.action)"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-white/10 bg-[#111111] p-6 shadow-2xl"
            @click.outside="cancel()"
        >
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-500/15">
                    <x-lucide-trash-2 class="h-5 w-5 text-red-400" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Delete sauce request</h2>
                    <p class="mt-1 text-sm text-gray-400" x-text="message"></p>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <button
                    type="button"
                    @click="cancel()"
                    class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="confirm()"
                    class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
@endsection