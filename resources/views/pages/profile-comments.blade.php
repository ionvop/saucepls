@extends('layouts.app')

@section('title', 'Comments - ' . $user->username . ' - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('profile.show', $user->username) }}"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-white">
                    <x-lucide-arrow-left class="h-4 w-4" />
                    {{ $user->username }}
                </a>
                <h1 class="mt-1 text-xl font-bold text-white">
                    {{ $comments->total() }} Comment{{ $comments->total() === 1 ? '' : 's' }}
                </h1>
            </div>
        </div>

        @if ($comments->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                <x-lucide-message-square class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                <p>{{ $user->username }} has no comments yet.</p>
            </div>
        @else
            <div class="mt-6 flex flex-col gap-4">
                @foreach ($comments as $comment)
                    <div class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
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

                            @if (isset($comment->likes_count) && $comment->likes_count > 0)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                                    <x-lucide-heart class="h-3.5 w-3.5" />
                                    {{ $comment->likes_count }}
                                </span>
                            @endif
                        </div>

                        {{-- Comment body --}}
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $comment->content }}</p>

                        {{-- Link to the parent request --}}
                        @if ($comment->sauceRequest)
                            <a href="{{ route('sauce-requests.show', $comment->sauceRequest) }}"
                                class="mt-3 inline-flex items-center gap-1.5 border-t border-white/10 pt-3 text-sm text-gray-400 transition hover:text-white w-full">
                                <span class="truncate">
                                    {{ $comment->sauceRequest->title }}
                                </span>
                                <x-lucide-external-link class="ml-auto h-3.5 w-3.5 shrink-0" />
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $comments->links() }}
            </div>
        @endif
    </div>
@endsection