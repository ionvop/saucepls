@extends('layouts.app')

@section('title', 'Subscription feed - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-3xl">
        <div>
            <h1 class="text-xl font-bold text-white">Subscription feed</h1>
            <p class="mt-1 text-sm text-gray-400">
                Sauce requests from the people you follow. This page is coming soon.
            </p>
        </div>

        <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-10 text-center text-sm text-gray-500">
            <x-lucide-bell class="mx-auto mb-3 h-10 w-10 text-gray-600" />
            <p>Your subscription feed will appear here.</p>
        </div>
    </div>
@endsection