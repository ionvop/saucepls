@extends('layouts.app')

@section('title', 'Sauce Requests - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-white">Sauce Requests</h1>
                <p class="mt-1 text-sm text-gray-400">Help the community find the source of these images.</p>
            </div>

            @auth
                <a href="{{ route('create') }}"
                    class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                    <x-lucide-plus class="h-4 w-4" />
                    New Request
                </a>
            @endauth
        </div>

        @if ($sauceRequests->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
                <x-lucide-image class="mx-auto mb-3 h-10 w-10 text-gray-600" />
                <p>No sauce requests yet.</p>
                @auth
                    <a href="{{ route('create') }}" class="mt-2 inline-block text-[#8888CC] hover:text-white">
                        Be the first to post one →
                    </a>
                @endauth
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