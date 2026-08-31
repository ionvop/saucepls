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
                                    title: 'Delete sauce request',
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
                    <span data-time="{{ $sauceRequest->created_at?->toIso8601String() }}" data-format="date">{{ $sauceRequest->created_at?->format('M j, Y') }}</span>
                </div>

                {{-- Description --}}
                @if ($sauceRequest->description)
                    <div class="mt-6 border-t border-white/10 pt-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Description</h2>
                        <p class="mt-2 whitespace-pre-line text-gray-300">{{ $sauceRequest->description }}</p>
                    </div>
                @endif

                {{-- OCR text --}}
                <div class="mt-6 border-t border-white/10 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Text in image</h2>

                    @auth
                        <div x-data="{ editing: false }">
                            {{-- Display mode: plain text + edit button --}}
                            <div x-show="!editing" x-cloak>
                                @if ($sauceRequest->text)
                                    <p class="mt-2 whitespace-pre-line text-gray-300">{{ $sauceRequest->text }}</p>
                                @else
                                    <p class="mt-2 text-sm text-gray-500">No text detected.</p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <button type="button" @click="editing = true"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-2.5 py-1 text-xs font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                                        <x-lucide-pencil class="h-3.5 w-3.5" />
                                        Edit text
                                    </button>

                                    <a href="{{ route('sauce-requests.text.history', $sauceRequest) }}"
                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400 transition hover:text-white">
                                        <x-lucide-history class="h-3.5 w-3.5" />
                                        View text history
                                    </a>
                                </div>
                            </div>

                            {{-- Edit mode: single editable field replacing the whole text --}}
                            <form x-show="editing" x-cloak
                                method="POST" action="{{ route('sauce-requests.text.update', $sauceRequest) }}"
                                class="mt-3 flex flex-col gap-2">
                                @csrf
                                @method('PUT')

                                <textarea name="text" rows="4" maxlength="5000"
                                    placeholder="Any visible text detected in the image."
                                    class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">{{ old('text', $sauceRequest->text) }}</textarea>
                                <p class="text-xs text-gray-500">
                                    Replaces the extracted text. Edit or clear it as needed.
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

                                @error('text')
                                    <p class="text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    @else
                        {{-- Guest: read-only text --}}
                        @if ($sauceRequest->text)
                            <p class="mt-2 whitespace-pre-line text-gray-300">{{ $sauceRequest->text }}</p>
                        @else
                            <p class="mt-2 text-sm text-gray-500">No text detected.</p>
                        @endif
                    @endauth
                </div>

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

                                <textarea name="tags" rows="2"
                                    placeholder="Space-separated tags (e.g. 1girl black_hair smile)" maxlength="1000"
                                    class="w-full resize-y rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">{{ old('tags', $sauceRequest->tags->pluck('name')->implode(' ')) }}</textarea>
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

                {{-- Answers --}}
                <div class="mt-6 border-t border-white/10 pt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">
                            Answers
                            @if ($sauceRequest->answers->isNotEmpty())
                                <span class="text-gray-500">({{ $sauceRequest->answers->count() }})</span>
                            @endif
                        </h2>

                        @if ($sauceRequest->answers->isNotEmpty())
                            <div class="flex items-center gap-1 rounded-lg border border-white/10 p-0.5 text-xs font-medium">
                                <a href="{{ route('sauce-requests.show', ['sauceRequest' => $sauceRequest, 'sort' => 'likes']) }}"
                                    class="rounded-md px-2.5 py-1 transition {{ $sort === 'likes' ? 'bg-[#5555AA] text-white' : 'text-gray-400 hover:text-white' }}">
                                    Most liked
                                </a>
                                <a href="{{ route('sauce-requests.show', ['sauceRequest' => $sauceRequest, 'sort' => 'recent']) }}"
                                    class="rounded-md px-2.5 py-1 transition {{ $sort === 'recent' ? 'bg-[#5555AA] text-white' : 'text-gray-400 hover:text-white' }}">
                                    Most recent
                                </a>
                            </div>
                        @endif
                    </div>

                    @auth
                        <form method="POST" action="{{ route('sauce-requests.answers.store', $sauceRequest) }}"
                            class="mt-4 flex flex-col gap-2">
                            @csrf

                            <textarea name="content" rows="3" maxlength="5000"
                                placeholder="Know the sauce? Share it here..."
                                class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">{{ old('content') }}</textarea>

                            <input type="url" name="url" maxlength="2048"
                                placeholder="Source link (optional) — e.g. https://x.com/..."
                                class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40" value="{{ old('url') }}">

                            @error('content')
                                <p class="text-xs text-red-400">{{ $message }}</p>
                            @enderror
                            @error('url')
                                <p class="text-xs text-red-400">{{ $message }}</p>
                            @enderror

                            <div class="flex items-center justify-end">
                                <button type="submit"
                                    class="rounded-lg bg-[#5555AA] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                    Post answer
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-gray-500">
                            <a href="{{ route('login') }}" class="font-medium text-[#8888CC] hover:text-white">Log in</a>
                            to provide an answer.
                        </p>
                    @endauth

                    @if ($sauceRequest->answers->isNotEmpty())
                        <div class="mt-6 flex flex-col gap-4">
                            @foreach ($sauceRequest->answers as $answer)
                                @php
                                    $isAccepted = $sauceRequest->accepted_sauce === $answer->id;
                                @endphp
                                <div x-data
                                    class="rounded-xl border p-4 {{ $isAccepted ? 'border-green-500/40 bg-green-500/[0.06]' : 'border-white/10 bg-white/[0.02]' }}">
                                    {{-- Answer header --}}
                                    <div class="flex items-center gap-2 text-sm text-gray-400">
                                        @if ($isAccepted)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-semibold text-green-300">
                                                <x-lucide-check class="h-3.5 w-3.5" />
                                                Accepted
                                            </span>
                                        @endif

                                        @if ($answer->user?->avatar_url)
                                            <img src="{{ $answer->user->avatar_url }}" alt="{{ $answer->user->username }}"
                                                class="h-6 w-6 rounded-full object-cover">
                                        @else
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#5555AA]/20 text-xs font-bold text-[#8888CC]">
                                                {{ strtoupper(substr($answer->user?->username ?? '?', 0, 1)) }}
                                            </span>
                                        @endif
                                        <a href="{{ route('profile.show', $answer->user?->username ?? '') }}"
                                            class="font-medium text-gray-200 hover:text-white">
                                            {{ $answer->user?->username ?? 'Unknown' }}
                                        </a>
                                        <span>·</span>
                                        <span data-time="{{ $answer->created_at?->toIso8601String() }}" data-format="date">{{ $answer->created_at?->format('M j, Y') }}</span>

                                        @auth
                                            @if ($answer->liked_by_me)
                                                <form method="POST"
                                                    action="{{ route('sauce-requests.answers.unlike', [$sauceRequest, $answer]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Unlike"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-[#8888CC] transition hover:text-white">
                                                        <x-lucide-heart class="h-3.5 w-3.5 fill-current" />
                                                        {{ $answer->likes_count }}
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('sauce-requests.answers.like', [$sauceRequest, $answer]) }}">
                                                    @csrf
                                                    <button type="submit" title="Like"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-[#8888CC]">
                                                        <x-lucide-heart class="h-3.5 w-3.5" />
                                                        {{ $answer->likes_count }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth

                                        @if ($isOwner || $isStaff)
                                            <div class="ml-auto flex items-center gap-2">
                                                @if ($isAccepted)
                                                    <form method="POST"
                                                        action="{{ route('sauce-requests.answers.unaccept', [$sauceRequest, $answer]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 text-xs font-medium text-green-300 transition hover:text-green-200">
                                                            <x-lucide-undo-2 class="h-3.5 w-3.5" />
                                                            Un-accept
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST"
                                                        action="{{ route('sauce-requests.answers.accept', [$sauceRequest, $answer]) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 text-xs font-medium text-gray-400 transition hover:text-green-300">
                                                            <x-lucide-check class="h-3.5 w-3.5" />
                                                            Accept
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($answer->user_id === auth()->id() || $isStaff)
                                                    <form x-ref="deleteAnswerForm" method="POST"
                                                        action="{{ route('sauce-requests.answers.destroy', [$sauceRequest, $answer]) }}"
                                                        class="hidden">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                    <button type="button"
                                                        @click="$dispatch('open-confirm', {
                                                            title: 'Delete answer',
                                                            message: 'Delete this answer? This cannot be undone.',
                                                            action: () => $refs.deleteAnswerForm.submit(),
                                                        })"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-red-400">
                                                        <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                                        Delete
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Answer body --}}
                                    <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $answer->content }}</p>

                                    @if ($answer->url)
                                        <a href="{{ $answer->url }}" target="_blank" rel="noopener noreferrer"
                                            class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-[#8888CC] transition hover:text-white">
                                            <x-lucide-external-link class="h-3.5 w-3.5" />
                                            Source
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">No answers yet. Be the first to provide the sauce.</p>
                    @endif
                </div>

                {{-- Comments --}}
                <div class="mt-6 border-t border-white/10 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">
                        Comments
                        @if ($sauceRequest->comments->isNotEmpty())
                            <span class="text-gray-500">({{ $sauceRequest->comments->count() }})</span>
                        @endif
                    </h2>

                    @auth
                        <form method="POST" action="{{ route('sauce-requests.comments.store', $sauceRequest) }}"
                            class="mt-4 flex flex-col gap-2">
                            @csrf

                            <textarea name="content" rows="3" maxlength="5000"
                                placeholder="Share a thought or help identify the sauce..."
                                class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">{{ old('content') }}</textarea>

                            @error('content')
                                <p class="text-xs text-red-400">{{ $message }}</p>
                            @enderror

                            <div class="flex items-center justify-end">
                                <button type="submit"
                                    class="rounded-lg bg-[#5555AA] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                    Post comment
                                </button>
                            </div>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-gray-500">
                            <a href="{{ route('login') }}" class="font-medium text-[#8888CC] hover:text-white">Log in</a>
                            to join the discussion.
                        </p>
                    @endauth

                    @if ($sauceRequest->comments->isNotEmpty())
                        <div class="mt-6 flex flex-col gap-4">
                            @foreach ($sauceRequest->comments as $comment)
                                <div x-data class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
                                    {{-- Comment header --}}
                                    <div class="flex items-center gap-2 text-sm text-gray-400">
                                        @if ($comment->user?->avatar_url)
                                            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->username }}"
                                                class="h-6 w-6 rounded-full object-cover">
                                        @else
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#5555AA]/20 text-xs font-bold text-[#8888CC]">
                                                {{ strtoupper(substr($comment->user?->username ?? '?', 0, 1)) }}
                                            </span>
                                        @endif
                                        <a href="{{ route('profile.show', $comment->user?->username ?? '') }}"
                                            class="font-medium text-gray-200 hover:text-white">
                                            {{ $comment->user?->username ?? 'Unknown' }}
                                        </a>
                                        <span>·</span>
                                        <span data-time="{{ $comment->created_at?->toIso8601String() }}" data-format="date">{{ $comment->created_at?->format('M j, Y') }}</span>

                                        @auth
                                            @if ($comment->liked_by_me)
                                                <form method="POST"
                                                    action="{{ route('sauce-requests.comments.unlike', [$sauceRequest, $comment]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Unlike"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-[#8888CC] transition hover:text-white">
                                                        <x-lucide-heart class="h-3.5 w-3.5 fill-current" />
                                                        {{ $comment->likes_count }}
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('sauce-requests.comments.like', [$sauceRequest, $comment]) }}">
                                                    @csrf
                                                    <button type="submit" title="Like"
                                                        class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-[#8888CC]">
                                                        <x-lucide-heart class="h-3.5 w-3.5" />
                                                        {{ $comment->likes_count }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth

                                        @if ($comment->user_id === auth()->id() || $isStaff)
                                            <form x-ref="deleteCommentForm" method="POST"
                                                action="{{ route('sauce-requests.comments.destroy', [$sauceRequest, $comment]) }}"
                                                class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <button type="button"
                                                @click="$dispatch('open-confirm', {
                                                    title: 'Delete comment',
                                                    message: 'Delete this comment? This cannot be undone.',
                                                    action: () => $refs.deleteCommentForm.submit(),
                                                })"
                                                class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-red-400">
                                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                                Delete
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Comment body --}}
                                    <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $comment->content }}</p>

                                    {{-- Replies --}}
                                    @if ($comment->replies->isNotEmpty())
                                        <div class="mt-3 flex flex-col gap-3 border-l border-white/10 pl-4">
                                            @foreach ($comment->replies as $reply)
                                                <div x-data>
                                                    <div class="flex items-center gap-2 text-sm text-gray-400">
                                                        @if ($reply->user?->avatar_url)
                                                            <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->username }}"
                                                                class="h-5 w-5 rounded-full object-cover">
                                                        @else
                                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#5555AA]/20 text-[10px] font-bold text-[#8888CC]">
                                                                {{ strtoupper(substr($reply->user?->username ?? '?', 0, 1)) }}
                                                            </span>
                                                        @endif
                                                        <a href="{{ route('profile.show', $reply->user?->username ?? '') }}"
                                                            class="font-medium text-gray-200 hover:text-white">
                                                            {{ $reply->user?->username ?? 'Unknown' }}
                                                        </a>
                                                        <span>·</span>
                                                        <span data-time="{{ $reply->created_at?->toIso8601String() }}" data-format="date">{{ $reply->created_at?->format('M j, Y') }}</span>

                                                        @auth
                                                            @if ($reply->liked_by_me)
                                                                <form method="POST"
                                                                    action="{{ route('sauce-requests.comments.unlike', [$sauceRequest, $reply]) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" title="Unlike"
                                                                        class="inline-flex items-center gap-1 text-xs font-medium text-[#8888CC] transition hover:text-white">
                                                                        <x-lucide-heart class="h-3.5 w-3.5 fill-current" />
                                                                        {{ $reply->likes_count }}
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <form method="POST"
                                                                    action="{{ route('sauce-requests.comments.like', [$sauceRequest, $reply]) }}">
                                                                    @csrf
                                                                    <button type="submit" title="Like"
                                                                        class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-[#8888CC]">
                                                                        <x-lucide-heart class="h-3.5 w-3.5" />
                                                                        {{ $reply->likes_count }}
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endauth

                                                        @if ($reply->user_id === auth()->id() || $isStaff)
                                                            <form x-ref="deleteReplyForm" method="POST"
                                                                action="{{ route('sauce-requests.comments.destroy', [$sauceRequest, $reply]) }}"
                                                                class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                            <button type="button"
                                                                @click="$dispatch('open-confirm', {
                                                                    title: 'Delete reply',
                                                                    message: 'Delete this reply? This cannot be undone.',
                                                                    action: () => $refs.deleteReplyForm.submit(),
                                                                })"
                                                                class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-red-400">
                                                                <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                                                Delete
                                                            </button>
                                                        @endif
                                                    </div>
                                                    <p class="mt-1 whitespace-pre-line text-sm text-gray-300">{{ $reply->content }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Reply form --}}
                                    @auth
                                        <form method="POST"
                                            action="{{ route('sauce-requests.comments.store', $sauceRequest) }}"
                                            class="mt-3 flex flex-col gap-2">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <textarea name="content" rows="2" maxlength="5000"
                                                placeholder="Reply to {{ $comment->user?->username ?? 'this comment' }}..."
                                                class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40"></textarea>
                                            <div class="flex items-center justify-end">
                                                <button type="submit"
                                                    class="rounded-lg bg-[#5555AA] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                                    Reply
                                                </button>
                                            </div>
                                        </form>
                                    @endauth
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">No comments yet. Be the first to comment.</p>
                    @endif
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
        @open-confirm.window="ask($event.detail.message, $event.detail.title, $event.detail.action)"
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
                    <h2 class="text-lg font-bold text-white" x-text="title"></h2>
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