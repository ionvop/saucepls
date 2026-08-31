@extends('layouts.app')

@section('title', 'Text history - ' . $sauceRequest->title . ' - ' . config('app.name', 'SaucePls'))

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
                    <h1 class="text-xl font-bold text-white">Text history</h1>
                </div>

                <p class="mt-1 text-sm text-gray-400">
                    Every extracted-text change is logged and attributed to the user who made it.
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-lg border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-300">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($history->isEmpty())
                    <p class="mt-6 text-sm text-gray-500">No text changes have been recorded yet.</p>
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

                                <div class="mt-3">
                                    <span class="text-xs text-gray-500">Text after this change:</span>
                                    <div class="mt-1.5 rounded-lg border border-white/5 bg-[#0d0d0d] p-3">
                                        @if ($entry->text_snapshot)
                                            <p class="whitespace-pre-line text-sm text-gray-300">{{ $entry->text_snapshot }}</p>
                                        @else
                                            <span class="text-xs text-gray-500">No text.</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('sauce-requests.text.history.restore', [$sauceRequest, $entry]) }}">
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