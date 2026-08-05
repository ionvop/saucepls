@props([
    'href' => '#',
    'active' => false,
])

@php
    $classes = $active
        ? 'flex items-center gap-3 rounded-lg bg-[#5555AA]/20 px-3 py-2 text-sm font-medium text-white'
        : 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-400 transition hover:bg-white/5 hover:text-white';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <span class="shrink-0">
        {{ $icon ?? '' }}
    </span>
    <span>{{ $slot }}</span>
</a>
