@extends('layouts.app')

@section('title', 'Edit profile - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-white">
                <x-lucide-arrow-left class="h-4 w-4" />
                Back to profile
            </a>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <h1 class="text-xl font-bold text-white">Edit profile</h1>
            <p class="mt-1 text-sm text-gray-400">Update your avatar, bio, and username.</p>

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

            <form
                method="POST"
                action="{{ route('profile.update') }}"
                enctype="multipart/form-data"
                class="mt-6 space-y-6"
                x-data="profileForm('{{ $user->username }}')"
                x-ref="form"
                @submit.prevent="submit($event)"
            >
                @csrf
                @method('PUT')

                {{-- Avatar --}}
                <div x-data="{ preview: {{ $user->avatar_url ? "'{$user->avatar_url}'" : 'null' }} }">
                    <label class="mb-2 block text-sm font-medium text-gray-300">Avatar</label>
                    <div class="flex items-center gap-4">
                        <template x-if="preview">
                            <img :src="preview" alt="Avatar preview" class="h-20 w-20 rounded-full border-2 border-[#5555AA]/40 object-cover">
                        </template>
                        <template x-if="!preview">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-[#5555AA]/40 bg-[#5555AA]/20 text-2xl font-bold text-[#8888CC]">
                                {{ strtoupper(substr($user->username, 0, 1)) }}
                            </div>
                        </template>

                        <div class="flex-1">
                            <input
                                type="file"
                                name="avatar"
                                id="avatar"
                                accept="image/*"
                                @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); }"
                                class="block w-full text-sm text-gray-400 file:mr-3 file:rounded-lg file:border-0 file:bg-[#5555AA]/20 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#8888CC] hover:file:bg-[#5555AA]/30"
                            >
                            <p class="mt-1 text-xs text-gray-500">JPEG, PNG, WebP, or GIF. Max 2MB.</p>
                            @error('avatar')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Username --}}
                <div x-data="usernameCooldown({{ $canChangeUsername ? 'true' : 'false' }}, '{{ $usernameAvailableAt->toIso8601String() }}')">
                    <label for="username" class="mb-1 block text-sm font-medium text-gray-300">Username</label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        value="{{ old('username', $user->username) }}"
                        :disabled="!canChange"
                        minlength="3"
                        maxlength="30"
                        autocomplete="username"
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                    <p class="mt-1 text-xs text-gray-500">Letters, numbers, hyphens, and underscores only.</p>

                    <template x-if="!canChange">
                        <p class="mt-2 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-300">
                            You can change your username again in
                            <span x-text="remaining" class="font-semibold"></span>.
                        </p>
                    </template>

                    @error('username')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-gray-300">Bio</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="6"
                        placeholder="Tell people about yourself... (Markdown supported)"
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40"
                    >{{ old('description', $user->description) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Markdown is supported.</p>
                    @error('description')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('profile') }}" class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#6666BB]">
                        Save changes
                    </button>
                </div>

                {{-- Username change confirmation modal --}}
                <div
                    x-show="showConfirm"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
                    @keydown.escape.window="cancel()"
                >
                    <div
                        class="w-full max-w-md rounded-2xl border border-white/10 bg-[#111111] p-6 shadow-2xl"
                        @click.outside="cancel()"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500/15">
                                <x-lucide-alert-triangle class="h-5 w-5 text-amber-400" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-white">Change username?</h2>
                                <p class="mt-1 text-sm text-gray-400">
                                    Changing your username will start a
                                    <span class="font-semibold text-gray-200">5-minute cooldown</span>
                                    before you can change it again.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button
                                type="button"
                                @click="cancel()"
                                class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                @click="confirmSubmit()"
                                class="rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#6666BB]"
                            >
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function profileForm(originalUsername) {
            return {
                originalUsername: originalUsername,
                showConfirm: false,
                pendingEvent: null,
                submit(event) {
                    const usernameInput = this.$refs.form.querySelector('input[name="username"]');
                    const usernameChanged = usernameInput && usernameInput.value !== this.originalUsername;

                    if (usernameChanged) {
                        this.pendingEvent = event;
                        this.showConfirm = true;
                        return;
                    }

                    this.$refs.form.submit();
                },
                confirmSubmit() {
                    this.showConfirm = false;
                    this.pendingEvent = null;
                    this.$refs.form.submit();
                },
                cancel() {
                    this.showConfirm = false;
                    this.pendingEvent = null;
                },
            };
        }

        function usernameCooldown(canChange, availableAtIso) {
            return {
                canChange: canChange,
                remaining: '',
                timer: null,
                init() {
                    if (this.canChange) return;
                    this.tick();
                    this.timer = setInterval(() => this.tick(), 1000);
                },
                tick() {
                    const availableAt = new Date(availableAtIso);
                    const diff = availableAt - Date.now();
                    if (diff <= 0) {
                        this.canChange = true;
                        clearInterval(this.timer);
                        return;
                    }
                    const totalSeconds = Math.ceil(diff / 1000);
                    const m = Math.floor(totalSeconds / 60);
                    const s = totalSeconds % 60;
                    this.remaining = `${m}m ${s}s`;
                },
            };
        }
    </script>
@endsection
