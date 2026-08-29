@extends('layouts.app')

@section('title', 'New Request - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('sauce-requests.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
            <x-lucide-arrow-left class="h-4 w-4" />
            Back to sauce requests
        </a>

        <div class="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <h1 class="text-xl font-bold text-white">New Sauce Request</h1>
            <p class="mt-1 text-sm text-gray-400">
                Upload an image you want the source of. The community will help you find it.
            </p>

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

            <form method="POST" action="{{ route('sauce-requests.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf

                {{-- Image --}}
                <div>
                    <label for="image" class="mb-1 block text-sm font-medium text-gray-300">Image</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp,image/gif" required
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white file:mr-3 file:rounded-lg file:border-0 file:bg-[#5555AA]/20 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-[#8888CC] hover:file:bg-[#5555AA]/30">
                    <p class="mt-1 text-xs text-gray-500">JPEG, PNG, WebP, or GIF. Max 10MB.</p>
                    @error('image')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Title --}}
                <div>
                    <label for="title" class="mb-1 block text-sm font-medium text-gray-300">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" maxlength="120"
                        placeholder="Who drew this?"
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">
                    <p class="mt-1 text-xs text-gray-500">Defaults to "Sauce pls" if left blank.</p>
                    @error('title')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-gray-300">Description</label>
                    <textarea name="description" id="description" rows="4" maxlength="5000"
                        placeholder="Any additional context, e.g. where you found the image."
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tags --}}
                <div>
                    <label for="tags" class="mb-1 block text-sm font-medium text-gray-300">Tags</label>
                    <input type="text" name="tags" id="tags" value="{{ old('tags') }}" maxlength="1000"
                        placeholder="1girl black_hair smile"
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">
                    <p class="mt-1 text-xs text-gray-500">
                        Space-separated, lowercase, alphanumeric, hyphens, and underscores only.
                    </p>
                    @error('tags')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Explicit toggle --}}
                <div class="flex items-center justify-between rounded-lg border border-white/10 bg-[#111111] px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Explicit content</p>
                        <p class="text-xs text-gray-500">Mark this image as containing explicit content.</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="is_explicit" value="1" class="peer sr-only"
                            {{ old('is_explicit', true) ? 'checked' : '' }}>
                        <div class="h-6 w-11 rounded-full bg-white/10 transition peer-checked:bg-[#5555AA]"></div>
                        <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('sauce-requests.index') }}"
                        class="rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:text-white">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-[#5555AA] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#6666BB]">
                        Post request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection