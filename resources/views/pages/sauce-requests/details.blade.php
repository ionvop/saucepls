@extends('layouts.app')

@section('title', 'Review Request - ' . config('app.name', 'SaucePls'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('create') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
            <x-lucide-arrow-left class="h-4 w-4" />
            Back to upload
        </a>

        <div class="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8">
            <h1 class="text-xl font-bold text-white">Review your request</h1>
            <p class="mt-1 text-sm text-gray-400">
                We scanned your image and filled in the text and tags below. Review and edit them before posting.
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

            <form method="POST" action="{{ route('sauce-requests.publish', $sauceRequest) }}" class="mt-6 space-y-6">
                @csrf

                {{-- Image (read-only) --}}
                @if ($sauceRequest->image_url)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-300">Image</label>
                        <img src="{{ $sauceRequest->image_url }}" alt="{{ $sauceRequest->title }}"
                            class="max-h-64 w-full rounded-lg border border-white/10 object-contain bg-[#1a1a1a]">
                    </div>
                @endif

                {{-- OCR text --}}
                <div>
                    <label for="text" class="mb-1 block text-sm font-medium text-gray-300">Text in image</label>
                    <textarea name="text" id="text" rows="4" maxlength="5000"
                        placeholder="Any visible text detected in the image."
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">{{ old('text', $sauceRequest->text) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Automatically extracted from the image. Edit or clear it as needed.
                    </p>
                    @error('text')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tags --}}
                <div>
                    <label for="tags" class="mb-1 block text-sm font-medium text-gray-300">Tags</label>
                    <input type="text" name="tags" id="tags" value="{{ old('tags', $sauceRequest->tags->pluck('name')->implode(' ')) }}" maxlength="1000"
                        placeholder="1girl black_hair smile"
                        class="w-full rounded-lg border border-white/10 bg-[#111111] px-3 py-2 text-sm text-white placeholder-gray-500 outline-none transition focus:border-[#5555AA] focus:ring-2 focus:ring-[#5555AA]/40">
                    <p class="mt-1 text-xs text-gray-500">
                        Suggested by our model. Space-separated, lowercase, alphanumeric, hyphens, and underscores only.
                    </p>
                    @error('tags')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('create') }}"
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