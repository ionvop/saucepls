@props([
    'title',
    'viewAllHref',
    'sauceRequests',
    'emptyMessage' => 'No requests yet.',
])

<section class="mt-8">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-bold text-white">{{ $title }}</h2>
        <a href="{{ $viewAllHref }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-[#8888CC] transition hover:text-[#9999DD]">
            View all
            <x-lucide-arrow-right class="h-4 w-4" />
        </a>
    </div>

    @if ($sauceRequests->isEmpty())
        <div class="mt-4 rounded-2xl border border-dashed border-white/10 p-8 text-center text-sm text-gray-500">
            <x-lucide-image class="mx-auto mb-3 h-8 w-8 text-gray-600" />
            <p>{{ $emptyMessage }}</p>
        </div>
    @else
        <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($sauceRequests as $sauceRequest)
                <x-sauce-request-card :sauceRequest="$sauceRequest" />
            @endforeach
        </div>
    @endif
</section>