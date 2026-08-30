@extends('layouts.app')

@section('title', 'Settings - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <h1 class="text-xl font-bold text-white">Settings</h1>
            <p class="mt-1 text-sm text-gray-400">Manage your preferences.</p>

            @if (session('status'))
                <div class="mt-4 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @auth
                <form
                    method="POST"
                    action="{{ route('settings.update') }}"
                    class="mt-6 space-y-6"
                >
                    @csrf
                    @method('PUT')

                    {{-- Hide NSFW toggle --}}
                    <div class="flex items-center justify-between rounded-lg border border-white/10 bg-[#111111] px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-300">Hide NSFW content</p>
                            <p class="text-xs text-gray-500">Hide explicit content from the feed.</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="hide_nsfw" value="1" class="peer sr-only"
                                {{ old('hide_nsfw', $user->hide_nsfw) ? 'checked' : '' }}>
                            <div class="h-6 w-11 rounded-full bg-white/10 transition peer-checked:bg-[#5555AA]"></div>
                            <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('home') }}" class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                            Cancel
                        </a>
                        <button type="submit" class="rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#6666BB]">
                            Save changes
                        </button>
                    </div>
                </form>
            @else
                {{-- Guest: client-side preference stored in the `hide_nsfw` cookie,
                     which the feed already reads. No account or server round-trip. --}}
                <div
                    x-data="guestNsfwToggle"
                    class="mt-6 space-y-6"
                >
                    <div class="flex items-center justify-between rounded-lg border border-white/10 bg-[#111111] px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-300">Hide NSFW content</p>
                            <p class="text-xs text-gray-500">Hide explicit content from the feed. Stored in your browser.</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" x-model="hideNsfw" @change="save()" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-white/10 transition peer-checked:bg-[#5555AA]"></div>
                            <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                        </label>
                    </div>

                    <p class="text-xs text-gray-500">
                        Sign in to sync this preference to your account.
                    </p>
                </div>
            @endauth
        </div>
    </div>
@endsection
