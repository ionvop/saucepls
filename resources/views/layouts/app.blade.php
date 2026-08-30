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

                            {{-- Avatar dropdown / profile card --}}
                            <div class="relative" x-data="{ open: false, showLogout: false }">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    title="{{ auth()->user()->username }}"
                                    class="flex items-center gap-1.5 rounded-full transition hover:opacity-90"
                                >
                                    <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-[#5555AA]/20 text-sm font-bold text-[#8888CC]">
                                        @if (auth()->user()->avatar_url)
                                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}" class="h-full w-full object-cover">
                                        @else
                                            {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                                        @endif
                                    </span>
                                    <x-lucide-chevron-down class="h-4 w-4 text-gray-400" />
                                </button>

                                {{-- Logout form --}}
                                <form x-ref="logoutForm" method="POST" action="{{ route('logout') }}" class="hidden">
                                    @csrf
                                </form>

                                {{-- Profile card dropdown --}}
                                <div
                                    x-show="open"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-1"
                                    @click.outside="open = false"
                                    @keydown.escape.window="open = false"
                                    class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-white/10 bg-[#111111] shadow-2xl"
                                >
                                    <div class="border-b border-white/10 p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#5555AA]/20 text-sm font-bold text-[#8888CC]">
                                                @if (auth()->user()->avatar_url)
                                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}" class="h-full w-full object-cover">
                                                @else
                                                    {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->username }}</p>
                                                <p class="truncate text-xs text-gray-400">{{ auth()->user()->email }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-1.5">
                                        <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-white/5 hover:text-white">
                                            <x-lucide-user class="h-4 w-4 text-gray-400" />
                                            Profile
                                        </a>
                                        <a href="{{ route('settings') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-300 transition hover:bg-white/5 hover:text-white">
                                            <x-lucide-settings class="h-4 w-4 text-gray-400" />
                                            Settings
                                        </a>
                                        <div class="my-1 border-t border-white/10"></div>
                                        <button
                                            type="button"
                                            @click="open = false; showLogout = true"
                                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-red-400 transition hover:bg-red-500/10 hover:text-red-300"
                                        >
                                            <x-lucide-log-out class="h-4 w-4" />
                                            Log out
                                        </button>
                                    </div>
                                </div>

                                {{-- Logout confirmation modal --}}
                                {{-- x-teleport="body" escapes the header's backdrop-filter containing
                                     block, which would otherwise size this fixed overlay to the header. --}}
                                <template x-teleport="body">
                                    <div
                                        x-show="showLogout"
                                        x-cloak
                                        x-transition.opacity
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
                                        @keydown.escape.window="showLogout = false"
                                    >
                                        <div
                                            class="w-full max-w-md rounded-2xl border border-white/10 bg-[#111111] p-6 shadow-2xl"
                                            @click.outside="showLogout = false"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-500/15">
                                                    <x-lucide-log-out class="h-5 w-5 text-red-400" />
                                                </div>
                                                <div>
                                                    <h2 class="text-lg font-bold text-white">Log out?</h2>
                                                    <p class="mt-1 text-sm text-gray-400">
                                                        You will need to sign in again to access your account.
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-6 flex items-center justify-end gap-3">
                                                <button
                                                    type="button"
                                                    @click="showLogout = false"
                                                    class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white"
                                                >
                                                    Cancel
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="$refs.logoutForm.submit()"
                                                    class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-400"
                                                >
                                                    Log out
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
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

                    <x-nav-link :href="route('sauce-requests.index')" :active="request()->routeIs('sauce-requests.*')">
                        <x-slot:icon><x-lucide-image class="h-5 w-5" /></x-slot:icon>
                        Sauce Requests
                    </x-nav-link>

                    <x-nav-link :href="route('notifications')" :active="request()->routeIs('notifications')">
                        <x-slot:icon><x-lucide-bell class="h-5 w-5" /></x-slot:icon>
                        Notifications
                    </x-nav-link>

                    @auth
                        <x-nav-link :href="route('profile')" :active="request()->routeIs('profile')">
                            <x-slot:icon><x-lucide-user class="h-5 w-5" /></x-slot:icon>
                            Profile
                        </x-nav-link>
                    @endauth

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

                <a href="{{ route('sauce-requests.index') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('sauce-requests.*') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-image class="h-5 w-5" />
                    <span>Requests</span>
                </a>

                <a href="{{ route('create') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('create') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-plus class="h-5 w-5" />
                    <span>New</span>
                </a>

                <a href="{{ route('notifications') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('notifications') ? 'text-[#5555AA]' : '' }}">
                    <x-lucide-bell class="h-5 w-5" />
                    <span>Alerts</span>
                </a>

                @auth
                    <a href="{{ route('profile') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-xs text-gray-400 transition hover:text-white {{ request()->routeIs('profile') ? 'text-[#5555AA]' : '' }}">
                        <x-lucide-user class="h-5 w-5" />
                        <span>Profile</span>
                    </a>
                @endauth
            </div>
        </nav>

        {{-- First-visit explicit-content warning dialog (guests who haven't chosen yet) --}}
        @guest
            @if (request()->cookie('hide_nsfw') === null)
                <div
                    x-data="explicitContentDialog"
                    x-show="show"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
                    @keydown.escape.window="dismiss()"
                >
                    <div
                        class="w-full max-w-md rounded-2xl border border-white/10 bg-[#111111] p-6 shadow-2xl"
                        @click.outside="dismiss()"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500/15">
                                <x-lucide-alert-triangle class="h-5 w-5 text-amber-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-white">Explicit content</h2>
                                <p class="mt-1 text-sm text-gray-400">
                                    This site may contain explicit or adult content. Would you like to hide it?
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <button
                                type="button"
                                @click="choose(true)"
                                class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white"
                            >
                                Hide explicit content
                            </button>
                            <button
                                type="button"
                                @click="choose(false)"
                                class="rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#6666BB]"
                            >
                                Show everything
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endguest
    </body>
</html>
