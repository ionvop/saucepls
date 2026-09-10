@extends('layouts.app')

@section('title', $user->username . ' - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        {{-- Profile header --}}
        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center">
                {{-- Avatar --}}
                <div class="relative shrink-0">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->username }}'s avatar"
                            class="h-24 w-24 rounded-full border-2 border-[#5555AA]/40 object-cover">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-full border-2 border-[#5555AA]/40 bg-[#5555AA]/20 text-3xl font-bold text-[#8888CC]">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Online status --}}
                    @if ($user->last_seen_at && $user->last_seen_at->gt(now()->subMinutes(5)))
                        <span class="absolute bottom-1 right-1 h-5 w-5 rounded-full border-4 border-[#111111] bg-green-500" title="Online"></span>
                    @endif
                </div>

                {{-- Identity --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold text-white">{{ $user->username }}</h1>

                        @if ($user->type !== 'member')
                            <span class="rounded-full bg-[#5555AA]/20 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-[#8888CC]">
                                {{ $user->type }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-sm text-gray-400">
                        Joined <span data-time="{{ $user->created_at?->toIso8601String() }}" data-format="month-year">{{ $user->created_at?->format('F Y') }}</span>
                    </p>
                </div>

                {{-- Actions --}}
                <div class="shrink-0">
                    @if ($isOwner)
                        <a href="{{ route('profile.edit') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                            <x-lucide-pencil class="h-4 w-4" />
                            Edit profile
                        </a>
                    @else
                        @auth
                            @if ($isFollowing)
                                <form method="POST" action="{{ route('profile.unfollow', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-white transition hover:bg-white/10">
                                        <x-lucide-user-check class="h-4 w-4" />
                                        Following
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('profile.follow', $user) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                        <x-lucide-user-plus class="h-4 w-4" />
                                        Follow
                                    </button>
                                </form>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>

            {{-- Profile statistics (top of bio) --}}
            <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-white/10 pt-5">
                <a href="{{ route('profile.accepted-answers', $user) }}"
                    class="group inline-flex items-baseline gap-1.5 text-sm">
                    <span class="text-xl font-bold text-white">{{ $user->accepted_answers_count }}</span>
                    <span class="text-gray-400 transition group-hover:text-white">Accepted sauce answers</span>
                </a>
                <a href="{{ route('profile.followers', $user) }}"
                    class="group inline-flex items-baseline gap-1.5 text-sm">
                    <span class="text-xl font-bold text-white">{{ $user->followers_count }}</span>
                    <span class="text-gray-400 transition group-hover:text-white">Followers</span>
                </a>
                <a href="{{ route('profile.following', $user) }}"
                    class="group inline-flex items-baseline gap-1.5 text-sm">
                    <span class="text-xl font-bold text-white">{{ $user->follows_count }}</span>
                    <span class="text-gray-400 transition group-hover:text-white">Following</span>
                </a>
            </div>

            {{-- Bio --}}
            <div class="prose prose-invert mt-5 max-w-none border-t border-white/10 pt-5 text-gray-300">
                {!! $bioHtml !!}
            </div>
        </div>

        {{-- Sauce requests made by this user --}}
        <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.02] p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-white">
                    Sauce requests
                    @if ($user->sauce_requests_count > 0)
                        <span class="text-gray-500">({{ $user->sauce_requests_count }})</span>
                    @endif
                </h2>
                @if ($user->sauce_requests_count > 0)
                    <a href="{{ route('profile.requests', $user) }}"
                        class="text-sm font-medium text-[#8888CC] transition hover:text-white">
                        View all
                    </a>
                @endif
            </div>

            @if ($requests->isNotEmpty())
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($requests as $request)
                        <a href="{{ route('sauce-requests.show', $request) }}"
                            class="group flex items-stretch overflow-hidden rounded-xl border border-white/10 bg-white/[0.03] transition hover:border-[#5555AA]/40 hover:bg-white/[0.05]">
                            <div class="relative w-24 shrink-0 bg-[#1a1a1a]">
                                @if ($request->image_url)
                                    <img src="{{ $request->image_url }}" alt="{{ $request->title }}"
                                        class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-gray-600">
                                        <x-lucide-image class="h-6 w-6" />
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1 p-3">
                                <div class="flex items-center gap-1.5">
                                    @if ($request->isAccepted())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-semibold text-green-300">
                                            <x-lucide-check class="h-3 w-3" />
                                            Solved
                                        </span>
                                    @endif
                                    @if ($request->is_explicit)
                                        <span class="rounded-full bg-red-500/20 px-2.5 py-0.5 text-xs font-semibold text-red-300">
                                            NSFW
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-1 truncate text-sm font-semibold text-white group-hover:text-[#8888CC]">
                                    {{ $request->title }}
                                </h3>
                                <div class="mt-auto flex items-center justify-between pt-1 text-xs text-gray-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        @if ($request->user?->avatar_url)
                                            <img src="{{ $request->user->avatar_url }}" alt="{{ $request->user->username }}"
                                                class="h-5 w-5 rounded-full object-cover">
                                        @else
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#5555AA]/20 text-[10px] font-bold text-[#8888CC]">
                                                {{ strtoupper(substr($request->user?->username ?? '?', 0, 1)) }}
                                            </span>
                                        @endif
                                        {{ $request->user?->username ?? 'Unknown' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <span title="{{ $request->bookmarks_count }} bookmark{{ $request->bookmarks_count === 1 ? '' : 's' }}">
                                            <x-lucide-bookmark class="inline-block h-3.5 w-3.5" />
                                            <span class="align-middle">{{ $request->bookmarks_count }}</span>
                                        </span>
                                        <span class="text-gray-400">·</span>
                                        <span data-time="{{ $request->created_at?->toIso8601String() }}" data-format="relative">{{ $request->created_at?->diffForHumans() }}</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">No sauce requests yet.</p>
            @endif
        </div>

        {{-- Sauce requests bookmarked by this user --}}
        <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.02] p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-white">
                    Bookmarked sauce requests
                    @if ($user->bookmarks_count > 0)
                        <span class="text-gray-500">({{ $user->bookmarks_count }})</span>
                    @endif
                </h2>
                @if ($user->bookmarks_count > 0)
                    <a href="{{ route('profile.bookmarks', $user) }}"
                        class="text-sm font-medium text-[#8888CC] transition hover:text-white">
                        View all
                    </a>
                @endif
            </div>

            @if ($bookmarks->isNotEmpty())
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($bookmarks as $bookmark)
                        <a href="{{ route('sauce-requests.show', $bookmark->request) }}"
                            class="group flex items-stretch overflow-hidden rounded-xl border border-white/10 bg-white/[0.03] transition hover:border-[#5555AA]/40 hover:bg-white/[0.05]">
                            <div class="relative w-24 shrink-0 bg-[#1a1a1a]">
                                @if ($bookmark->request->image_url)
                                    <img src="{{ $bookmark->request->image_url }}" alt="{{ $bookmark->request->title }}"
                                        class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-gray-600">
                                        <x-lucide-image class="h-6 w-6" />
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1 p-3">
                                <div class="flex items-center gap-1.5">
                                    @if ($bookmark->request->isAccepted())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-semibold text-green-300">
                                            <x-lucide-check class="h-3 w-3" />
                                            Solved
                                        </span>
                                    @endif
                                    @if ($bookmark->request->is_explicit)
                                        <span class="rounded-full bg-red-500/20 px-2.5 py-0.5 text-xs font-semibold text-red-300">
                                            NSFW
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-1 truncate text-sm font-semibold text-white group-hover:text-[#8888CC]">
                                    {{ $bookmark->request->title }}
                                </h3>
                                <div class="mt-auto flex items-center justify-between pt-1 text-xs text-gray-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        @if ($bookmark->request->user?->avatar_url)
                                            <img src="{{ $bookmark->request->user->avatar_url }}" alt="{{ $bookmark->request->user->username }}"
                                                class="h-5 w-5 rounded-full object-cover">
                                        @else
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#5555AA]/20 text-[10px] font-bold text-[#8888CC]">
                                                {{ strtoupper(substr($bookmark->request->user?->username ?? '?', 0, 1)) }}
                                            </span>
                                        @endif
                                        {{ $bookmark->request->user?->username ?? 'Unknown' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <span title="{{ $bookmark->request->bookmarks_count }} bookmark{{ $bookmark->request->bookmarks_count === 1 ? '' : 's' }}">
                                            <x-lucide-bookmark class="inline-block h-3.5 w-3.5" />
                                            <span class="align-middle">{{ $bookmark->request->bookmarks_count }}</span>
                                        </span>
                                        <span class="text-gray-400">·</span>
                                        <span data-time="{{ $bookmark->request->created_at?->toIso8601String() }}" data-format="relative">{{ $bookmark->request->created_at?->diffForHumans() }}</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">No bookmarked sauce requests.</p>
            @endif
        </div>

        {{-- Sauce answers made by this user --}}
        <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.02] p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-white">
                    Sauce answers
                    @if ($user->sauce_answers_count > 0)
                        <span class="text-gray-500">({{ $user->sauce_answers_count }})</span>
                    @endif
                </h2>
                @if ($user->sauce_answers_count > 0)
                    <a href="{{ route('profile.answers', $user) }}"
                        class="text-sm font-medium text-[#8888CC] transition hover:text-white">
                        View all
                    </a>
                @endif
            </div>

            @if ($answers->isNotEmpty())
                <div class="mt-4 flex flex-col gap-3">
                    @foreach ($answers as $answer)
                        @php
                            $isAccepted = $answer->sauceRequest?->accepted_sauce === $answer->id;
                        @endphp
                        <div class="rounded-xl border p-4 {{ $isAccepted ? 'border-green-500/40 bg-green-500/[0.06]' : 'border-white/10 bg-white/[0.03]' }}">
                            <div class="text-sm text-gray-300 markdown-body">{!! $answer->content_html !!}</div>
                            @if ($answer->sauceRequest)
                                <a href="{{ route('sauce-requests.show', $answer->sauceRequest) }}"
                                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-[#8888CC] transition hover:text-white">
                                    <x-lucide-external-link class="h-3 w-3" />
                                    <span class="truncate">{{ $answer->sauceRequest->title }}</span>
                                </a>
                            @endif
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                @if ($isAccepted)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-semibold text-green-300">
                                        <x-lucide-check class="h-3.5 w-3.5" />
                                        Accepted
                                    </span>
                                @endif
                                <span data-time="{{ $answer->created_at?->toIso8601String() }}" data-format="relative">{{ $answer->created_at?->diffForHumans() }}</span>
                                @if (isset($answer->likes_count) && $answer->likes_count > 0)
                                    <span class="inline-flex items-center gap-1">
                                        <x-lucide-heart class="h-3 w-3" />
                                        {{ $answer->likes_count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">No sauce answers yet.</p>
            @endif
        </div>

        {{-- Comments made by this user --}}
        <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.02] p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-white">
                    Comments made
                    @if ($user->comments_count > 0)
                        <span class="text-gray-500">({{ $user->comments_count }})</span>
                    @endif
                </h2>
                @if ($user->comments_count > 0)
                    <a href="{{ route('profile.comments', $user) }}"
                        class="text-sm font-medium text-[#8888CC] transition hover:text-white">
                        View all
                    </a>
                @endif
            </div>

            @if ($comments->isNotEmpty())
                <div class="mt-4 flex flex-col gap-3">
                    @foreach ($comments as $comment)
                        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
                            <p class="whitespace-pre-line text-sm text-gray-300">{{ $comment->content }}</p>
                            @if ($comment->sauceRequest)
                                <a href="{{ route('sauce-requests.show', $comment->sauceRequest) }}"
                                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-[#8888CC] transition hover:text-white">
                                    <x-lucide-external-link class="h-3 w-3" />
                                    <span class="truncate">{{ $comment->sauceRequest->title }}</span>
                                </a>
                            @endif
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                <span data-time="{{ $comment->created_at?->toIso8601String() }}" data-format="relative">{{ $comment->created_at?->diffForHumans() }}</span>
                                @if (isset($comment->likes_count) && $comment->likes_count > 0)
                                    <span class="inline-flex items-center gap-1">
                                        <x-lucide-heart class="h-3 w-3" />
                                        {{ $comment->likes_count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500">No comments made yet.</p>
            @endif
        </div>

        {{-- Comments --}}
        <div class="mt-6 rounded-2xl border border-white/10 bg-white/[0.02] p-6">
            <h2 class="text-lg font-semibold text-white">
                Comments
                @if ($user->receivedProfileComments->isNotEmpty())
                    <span class="text-gray-500">({{ $user->receivedProfileComments->count() }})</span>
                @endif
            </h2>

            @auth
                <form method="POST" action="{{ route('profile.comments.store', $user) }}"
                    class="mt-4 flex flex-col gap-2">
                    @csrf
                    <textarea name="content" rows="3" maxlength="5000"
                        placeholder="Leave a comment on {{ $user->username }}'s profile..."
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40"></textarea>
                    @error('content')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                            Post comment
                        </button>
                    </div>
                </form>
            @else
                <p class="mt-4 text-sm text-gray-500">
                    <a href="{{ route('login') }}" class="font-medium text-[#8888CC] hover:text-white">Log in</a>
                    to leave a comment.
                </p>
            @endauth

            @if ($user->receivedProfileComments->isNotEmpty())
                <div class="mt-4 flex flex-col gap-4">
                    @foreach ($user->receivedProfileComments as $comment)
                        <div x-data="{ showReplyForm: false }" class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
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
                                <span data-time="{{ $comment->created_at?->toIso8601String() }}" data-format="relative">{{ $comment->created_at?->diffForHumans() }}</span>

                                @auth
                                    @if ($comment->liked_by_me)
                                        <form method="POST"
                                            action="{{ route('profile.comments.unlike', [$user, $comment]) }}">
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
                                            action="{{ route('profile.comments.like', [$user, $comment]) }}">
                                            @csrf
                                            <button type="submit" title="Like"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-[#8888CC]">
                                                <x-lucide-heart class="h-3.5 w-3.5" />
                                                {{ $comment->likes_count }}
                                            </button>
                                        </form>
                                    @endif
                                @endauth

                                @if ($comment->user_id === auth()->id() || $isOwner || $isStaff)
                                    <form x-ref="deleteProfileCommentForm" method="POST"
                                        action="{{ route('profile.comments.destroy', [$user, $comment]) }}"
                                        class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button"
                                        @click="$dispatch('open-confirm', {
                                            title: 'Delete comment',
                                            message: 'Delete this comment? This cannot be undone.',
                                            action: () => $refs.deleteProfileCommentForm.submit(),
                                        })"
                                        class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-red-400">
                                        <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                        <span class="hidden sm:inline">Delete</span>
                                    </button>
                                @endif
                            </div>

                            {{-- Comment body --}}
                            <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $comment->content }}</p>

                            {{-- Replies --}}
                            @if ($comment->replies->isNotEmpty())
                                <div class="mt-3 flex flex-col gap-3 border-l border-white/10 pl-4">
                                    @foreach ($comment->replies as $reply)
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
                                            <span data-time="{{ $reply->created_at?->toIso8601String() }}" data-format="relative">{{ $reply->created_at?->diffForHumans() }}</span>

                                            @auth
                                                @if ($reply->liked_by_me)
                                                    <form method="POST"
                                                        action="{{ route('profile.comments.unlike', [$user, $reply]) }}">
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
                                                        action="{{ route('profile.comments.like', [$user, $reply]) }}">
                                                        @csrf
                                                        <button type="submit" title="Like"
                                                            class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-[#8888CC]">
                                                            <x-lucide-heart class="h-3.5 w-3.5" />
                                                            {{ $reply->likes_count }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endauth

                                            @if ($reply->user_id === auth()->id() || $isOwner || $isStaff)
                                                <form x-ref="deleteProfileReplyForm" method="POST"
                                                    action="{{ route('profile.comments.destroy', [$user, $reply]) }}"
                                                    class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <button type="button"
                                                    @click="$dispatch('open-confirm', {
                                                        title: 'Delete comment',
                                                        message: 'Delete this comment? This cannot be undone.',
                                                        action: () => $refs.deleteProfileReplyForm.submit(),
                                                    })"
                                                    class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-red-400">
                                                    <x-lucide-trash-2 class="h-3.5 w-3.5" />
                                                    <span class="hidden sm:inline">Delete</span>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="mt-1 whitespace-pre-line text-sm text-gray-300">{{ $reply->content }}</p>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Reply form --}}
                            @auth
                                <button type="button"
                                    @click="showReplyForm = !showReplyForm"
                                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-[#8888CC] transition hover:text-white">
                                    <x-lucide-message-circle-plus class="h-3.5 w-3.5" />
                                    <span class="hidden sm:inline" x-text="showReplyForm ? 'Close' : 'Reply'"></span>
                                </button>

                                <form method="POST"
                                    action="{{ route('profile.comments.store', $user) }}"
                                    class="mt-2 flex flex-col gap-2"
                                    x-show="showReplyForm"
                                    x-cloak>
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea name="content" rows="2" maxlength="5000"
                                        placeholder="Reply to {{ $comment->user?->username ?? 'this comment' }}..."
                                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40"></textarea>
                                    @error('content')
                                        <p class="text-xs text-red-400">{{ $message }}</p>
                                    @enderror
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
@endsection
