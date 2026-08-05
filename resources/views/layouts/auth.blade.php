<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'SaucePls'))</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#111111] text-gray-100 antialiased min-h-screen">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2">
                <x-lucide-flame class="h-8 w-8 text-[#5555AA]" />
                <span class="text-2xl font-bold tracking-tight text-white">{{ config('app.name', 'SaucePls') }}</span>
            </a>

            <div class="w-full max-w-md rounded-2xl border border-white/10 bg-[#1a1a1a] p-8 shadow-xl">
                @yield('content')
            </div>
        </div>
    </body>
</html>
