@extends('layouts.app')

@section('title', 'Notifications - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-xl font-bold text-white">Notifications</h1>
        </div>

        @if ($notifications->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                <x-lucide-bell class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                <p>You don't have any notifications yet.</p>
            </div>
        @else
            <div class="mt-6 divide-y divide-white/10 rounded-2xl border border-white/10 bg-white/[0.03]">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $actor = $data['actor_username'] ?? null;
                        $message = $data['message'] ?? '';
                        $link = null;

                        if (isset($data['sauce_request_id'])) {
                            $link = route('sauce-requests.show', $data['sauce_request_id']);
                        } elseif (isset($data['profile_username'])) {
                            $link = route('profile.show', $data['profile_username']);
                        }
                    @endphp

                    <a href="{{ $link ?? '#' }}"
                        class="flex items-start gap-3 px-4 py-3 transition hover:bg-white/5 {{ $notification->read() ? 'opacity-60' : '' }}">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#5555AA]/20 text-sm font-bold text-[#8888CC]">
                            @if ($actor)
                                {{ strtoupper(substr($actor, 0, 1)) }}
                            @else
                                <x-lucide-bell class="h-4 w-4" />
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm text-gray-200">
                                @if ($actor)
                                    <span class="font-semibold text-white">{{ $actor }}</span>
                                @endif
                                {{ $message }}
                            </span>
                            <span class="mt-0.5 block text-xs text-gray-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </span>
                        @if ($notification->unread())
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[#5555AA]"></span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
