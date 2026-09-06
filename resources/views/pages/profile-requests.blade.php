@extends('layouts.app')

@section('title', $title . ' - ' . $user->username . ' - ' . config('app.name', 'SaucePls'))

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
                    {{ $sauceRequests->total() }} {{ $title }}
                </h1>
            </div>
        </div>

        @if ($sauceRequests->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                <x-lucide-image class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                <p>{{ $emptyMessage }}</p>
            </div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                @foreach ($sauceRequests as $sauceRequest)
                    <x-sauce-request-card :sauceRequest="$sauceRequest" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $sauceRequests->links() }}
            </div>
        @endif
    </div>
@endsection