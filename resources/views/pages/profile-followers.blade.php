@extends('layouts.app')

@section('title', ucfirst($type) . ' - ' . $user->username . ' - ' . config('app.name', 'SaucePls'))

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
                    {{ $users->total() }} {{ ucfirst($type) }}
                </h1>
            </div>
        </div>

        @if ($users->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                <x-lucide-users class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                <p>
                    @if ($type === 'followers')
                        No one follows {{ $user->username }} yet.
                    @else
                        {{ $user->username }} isn't following anyone yet.
                    @endif
                </p>
            </div>
        @else
            <div class="mt-6 overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03] divide-y divide-white/10">
                @foreach ($users as $followedUser)
                    <div class="flex items-center gap-4 p-4">
                        <a href="{{ route('profile.show', $followedUser->username) }}"
                            class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#5555AA]/20 text-sm font-bold text-[#8888CC]">
                                @if ($followedUser->avatar_url)
                                    <img src="{{ $followedUser->avatar_url }}" alt="{{ $followedUser->username }}" class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(substr($followedUser->username, 0, 1)) }}
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-white">{{ $followedUser->username }}</span>
                                <span class="block text-xs text-gray-400">{{ $followedUser->followers_count }} followers</span>
                            </span>
                        </a>

                        @auth
                            @if ($followedUser->isNot(auth()->user()))
                                @if (auth()->user()->follows()->where('followed_id', $followedUser->id)->exists())
                                    <form method="POST" action="{{ route('profile.unfollow', $followedUser) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-sm text-white transition hover:bg-white/10">
                                            Following
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('profile.follow', $followedUser) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#5555AA] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                            Follow
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection