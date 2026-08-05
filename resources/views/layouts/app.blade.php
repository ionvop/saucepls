<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'SaucePls'))</title>

        @fonts

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#111111] text-gray-100 antialiased min-h-screen">
        <div class="min-h-screen flex flex-col">
            {{-- Header --}}
            <header class="sticky top-0 z-40 border-b border-white/10 bg-[#111111]/90 backdrop-blur">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    {{-- Brand --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <x-lucide-flame class="h-7 w-7 text-[#5555AA]" />
                        <span class="text-lg font-bold tracking-tight text-white">{{ config('app.name', 'SaucePls') }}</span>
                    </a>

                    {{-- Header actions --}}
                    <div class="flex items-center gap-2">
                        {{-- Search shortcut (desktop) --}}
                        <a href="{{ route('search') }}" class="hidden items-center gap-2 rounded-lg border border-white/10 px-3 py-1.5 text-sm text-gray-400 transition hover:border-white/20 hover:text-gray-200 sm:flex">
                            <x-lucide-search class="h-4 w-4" />
                            <span>Search</span>
                        </a>

                        @auth
                            {{-- Create button --}}
                            <a href="{{ route('create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#5555AA] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#6666BB]">
                                <x-lucide-plus class="h-4 w-4" />
                                <span class="hidden sm:inline">New Request</span>
                            </a>

                            {{-- Avatar / profile --}}
                            <a href="{{ route('profile') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#5555AA]/20 text-sm font-bold text-[#8888CC] transition hover:bg-[#5555AA]/30" title="{{ auth()->user()->username }}">
                                {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/10 px-3 py-1.5 text-sm font-medium text-gray-200 transition hover:border-white/20 hover:text-white">
                                <x-lucide-log-in class="h-4 w-4" />
                                <span>Log in</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <div class="mx-auto flex w-full max-w-7xl flex-1 gap-6 px-4 sm:px-6 lg:px-8">
                {{-- Left navigation (desktop) --}}
                <aside class="sticky top-16 hidden h-[calc(100vh-4rem)] w-60 shrink-0 flex-col gap-1 overflow-y-auto py-6 lg:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        <x-slot:icon><x-lucide-home class="h-5 w-5" /></x-slot:icon>
                        Home
                    </x-nav-link>

                    <x-nav-link :href="route('search')" :active="request()->routeIs('search')">
                        <x-slot:icon><x-lucide-search class="h-5 w-5" /></x-slot:icon>
                        Search
                    </x-nav-link>

                    <x-nav-link :href="route('notifications')" :active="request()->routeIs('notifications')">
                        <x-slot:icon><x-lucide-bell class="h-5 w-5" /></x-slot:icon>
                        Notifications
                    </x-nav-link>

                    <x-nav-link :href="route('profile')" :active="request()->routeIs('profile')">
                        <x-slot:icon><x-lucide-user class="h-5 w-5" /></x-slot:icon>
                        Profile
                    </x-nav-link>

                    <div class="my-3 border-t border-white/10"></div>

                    <x-nav-link :href="route('create')" :active="request()->routeIs('create')">
                        <x-slot:icon><x-lucide-plus class="h-5 w-5" /></x-slot:icon>
                        New Request
                    </x-nav-link>

                    <x-nav-link :href="route('settings')" :active="request()->routeIs('settings')">
                        <x-slot:icon><x-lucide-settings class="h-5 w-5" /></x-slot:icon>
                        Settings
                    </x-nav-link>

                    @auth
                        <div class="my-3 border-t border-white/10"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-400 transition hover:bg-white/5 hover:text-red-300">
                                <x-lucide-log-out class="h-5 w-5" />
                                Log out
                            </button>
                        </form>
                    @endauth
                </aside>

                {{-- Main content --}}
                <main class="min-w-0 flex-1 py-6 pb-24 lg:pb-6">
                    @yield('content')
                </main>
            </div>
        </div>

        {{-- Bottom navigation (mobile) --}}
        <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-white/10 bg-[#111111]/95 backdrop-blur lg:hidden">
            <div class="mx-auto flex max-w-7xl items-center justify-around px-2 py-1">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('home') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-home class="h-5 w-5" />
                    <span>Home</span>
                </a>

                <a href="{{ route('search') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('search') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-search class="h-5 w-5" />
                    <span>Search</span>
                </a>

                <a href="{{ route('create') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('create') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-plus class="h-5 w-5" />
                    <span>New</span>
                </a>

                <a href="{{ route('notifications') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('notifications') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-bell class="h-5 w-5" />
                    <span>Alerts</span>
                </a>

                <a href="{{ route('profile') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('profile') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-user class="h-5 w-5" />
                    <span>Profile</span>
                </a>
            </div>
        </nav>
    </body>
</html>
