@extends('layouts.app')

@section('title', 'Accepted sauce answers - ' . $user->username . ' - ' . config('app.name', 'SaucePls'))

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
                    {{ $answers->total() }} Accepted sauce answer{{ $answers->total() === 1 ? '' : 's' }}
                </h1>
            </div>
        </div>

        @if ($answers->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                <x-lucide-check class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                <p>{{ $user->username }} has no accepted sauce answers yet.</p>
            </div>
        @else
            <div class="mt-6 flex flex-col gap-4">
                @foreach ($answers as $answer)
                    <div class="rounded-xl border border-green-500/40 bg-green-500/[0.06] p-4">
                        {{-- Answer header --}}
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <span class="inline-flex items-center gap-1 rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-semibold text-green-300">
                                <x-lucide-check class="h-3.5 w-3.5" />
                                Accepted
                            </span>

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
                            <span data-time="{{ $answer->created_at?->toIso8601String() }}" data-format="relative">{{ $answer->created_at?->diffForHumans() }}</span>

                            @if (isset($answer->likes_count) && $answer->likes_count > 0)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                                    <x-lucide-heart class="h-3.5 w-3.5" />
                                    {{ $answer->likes_count }}
                                </span>
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

                        {{-- Link to the parent request --}}
                        @if ($answer->sauceRequest)
                            <a href="{{ route('sauce-requests.show', $answer->sauceRequest) }}"
                                class="mt-3 inline-flex items-center gap-1.5 border-t border-white/10 pt-3 text-sm text-gray-400 transition hover:text-white w-full">
                                <span class="truncate">
                                    {{ $answer->sauceRequest->title }}
                                </span>
                                <x-lucide-external-link class="ml-auto h-3.5 w-3.5 shrink-0" />
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $answers->links() }}
            </div>
        @endif
    </div>
@endsection