@props(['sauceRequest'])

<a href="{{ route('sauce-requests.show', $sauceRequest) }}"
    class="group block overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03] transition hover:border-[#5555AA]/40 hover:bg-white/[0.05]">
    {{-- Image --}}
    <div class="relative aspect-[4/3] overflow-hidden bg-[#1a1a1a]">
        @if ($sauceRequest->image_url)
            <img src="{{ $sauceRequest->image_url }}" alt="{{ $sauceRequest->title }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-gray-600">
                <x-lucide-image class="h-10 w-10" />
            </div>
        @endif

        {{-- Accepted badge --}}
        @if ($sauceRequest->isAccepted())
            <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-green-500/90 px-2.5 py-0.5 text-xs font-semibold text-white">
                <x-lucide-check class="h-3.5 w-3.5" />
                Solved
            </span>
        @endif

        {{-- Explicit badge --}}
        @if ($sauceRequest->is_explicit)
            <span class="absolute right-3 top-3 rounded-full bg-red-500/90 px-2.5 py-0.5 text-xs font-semibold text-white">
                NSFW
            </span>
        @endif
    </div>

    {{-- Body --}}
    <div class="p-4">
        <h3 class="truncate text-sm font-semibold text-white group-hover:text-[#8888CC]">
            {{ $sauceRequest->title }}
        </h3>

        @if ($sauceRequest->description)
            <p class="mt-1 line-clamp-2 text-sm text-gray-400">{{ $sauceRequest->description }}</p>
        @endif

        <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span class="inline-flex items-center gap-1.5">
                @if ($sauceRequest->user?->avatar_url)
                    <img src="{{ $sauceRequest->user->avatar_url }}" alt="{{ $sauceRequest->user->username }}"
                        class="h-5 w-5 rounded-full object-cover">
                @else
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#5555AA]/20 text-[10px] font-bold text-[#8888CC]">
                        {{ strtoupper(substr($sauceRequest->user?->username ?? '?', 0, 1)) }}
                    </span>
                @endif
                {{ $sauceRequest->user?->username ?? 'Unknown' }}
            </span>
            <span data-time="{{ $sauceRequest->created_at?->toIso8601String() }}" data-format="relative">{{ $sauceRequest->created_at?->diffForHumans() }}</span>
        </div>
    </div>
</a>