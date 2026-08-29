@extends('layouts.app')

@section('title', 'Tagging history - ' . $sauceRequest->title . ' - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('sauce-requests.show', $sauceRequest) }}"
            class="inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
            <x-lucide-arrow-left class="h-4 w-4" />
            Back to {{ $sauceRequest->title }}
        </a>

        <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-2">
                    <x-lucide-history class="h-5 w-5 text-[#8888CC]" />
                    <h1 class="text-xl font-bold text-white">Tagging history</h1>
                </div>

                <p class="mt-1 text-sm text-gray-400">
                    Every tag change is logged and attributed to the user who made it.
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-lg border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-300">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($history->isEmpty())
                    <p class="mt-6 text-sm text-gray-500">No tagging changes have been recorded yet.</p>
                @else
                    <ol class="mt-6 space-y-4">
                        @foreach ($history as $entry)
                            <li class="rounded-xl border border-white/10 bg-[#111111] p-4">
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    @if ($entry->user?->avatar_url)
                                        <img src="{{ $entry->user->avatar_url }}" alt="{{ $entry->user->username }}"
                                            class="h-6 w-6 rounded-full object-cover">
                                    @else
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#5555AA]/20 text-xs font-bold text-[#8888CC]">
                                            {{ strtoupper(substr($entry->user?->username ?? '?', 0, 1)) }}
                                        </span>
                                    @endif
                                    <a href="{{ route('profile.show', $entry->user?->username ?? '') }}"
                                        class="font-medium text-gray-200 hover:text-white">
                                        {{ $entry->user?->username ?? 'Unknown' }}
                                    </a>
                                    <span class="text-gray-500">·</span>
                                    <span class="text-gray-400">{{ $entry->created_at?->format('M j, Y g:i A') }}</span>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    @if (! empty($entry->added_tags))
                                        <span class="text-xs text-gray-500">Added:</span>
                                        @foreach ($entry->added_tags as $tag)
                                            <span class="inline-flex items-center rounded-full bg-green-500/15 px-2.5 py-0.5 text-xs font-medium text-green-300">
                                                +{{ $tag }}
                                            </span>
                                        @endforeach
                                    @endif

                                    @if (! empty($entry->removed_tags))
                                        <span class="text-xs text-gray-500">Removed:</span>
                                        @foreach ($entry->removed_tags as $tag)
                                            <span class="inline-flex items-center rounded-full bg-red-500/15 px-2.5 py-0.5 text-xs font-medium text-red-300">
                                                -{{ $tag }}
                                            </span>
                                        @endforeach
                                    @endif

                                    @if (empty($entry->added_tags) && empty($entry->removed_tags))
                                        <span class="text-xs text-gray-500">No tag changes.</span>
                                    @endif
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('sauce-requests.tags.history.revert', [$sauceRequest, $entry]) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                                            <x-lucide-undo-2 class="h-3.5 w-3.5" />
                                            Revert this change
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('sauce-requests.tags.history.restore', [$sauceRequest, $entry]) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                                            <x-lucide-rotate-ccw class="h-3.5 w-3.5" />
                                            Restore to this state
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </div>
@endsection