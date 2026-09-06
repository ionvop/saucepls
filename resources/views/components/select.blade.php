@props([
    'name',
    'value' => '',
    'options' => [],
    'label' => null,
])

{{-- The options map gives: [ 'value' => 'Label', ... ] --}}
@php
    $selectedLabel = collect($options)->get($value) ?? collect($options)->first();
@endphp

<div
    class="relative inline-block"
    x-data="dropdown({
        name: @js($name),
        value: @js($value),
        options: @js($options),
        selectedLabel: @js($selectedLabel),
    })"
>
    @if ($label)
        <span class="mr-2 text-sm text-gray-400">{{ $label }}</span>
    @endif

    {{-- Hidden native select keeps form submission working --}}
    <select
        name="{{ $name }}"
        x-ref="select"
        x-model="value"
        class="sr-only"
        tabindex="-1"
        aria-hidden="true"
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}">
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    {{-- Visible trigger --}}
    <button
        type="button"
        x-ref="button"
        @click="toggle()"
        :aria-expanded="open"
        aria-haspopup="listbox"
        class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-1.5 text-sm text-white focus:border-[#5555AA]/60 focus:outline-none focus:ring-1 focus:ring-[#5555AA]/40"
    >
        <span x-text="selectedLabel"></span>
        <x-lucide-chevron-down class="h-4 w-4 text-gray-500" x-bind:class="{ 'rotate-180': open }" />
    </button>

    {{-- Listbox --}}
    <div
        x-show="open"
        x-transition.opacity.duration.150ms
        x-cloak
        @click.outside="close()"
        @keydown.escape.window="close()"
        role="listbox"
        class="absolute left-0 z-30 mt-1 min-w-full overflow-hidden rounded-lg border border-white/10 bg-[#1a1a1a] py-1 shadow-xl shadow-black/40"
    >
        <template x-for="(label, optionValue) in options" :key="optionValue">
            <button
                type="button"
                role="option"
                :aria-selected="value === optionValue"
                @click="select(optionValue)"
                @keydown.arrow-down.prevent="moveFocus(1)"
                @keydown.arrow-up.prevent="moveFocus(-1)"
                class="block w-full px-3 py-1.5 text-left text-sm transition"
                :class="value === optionValue
                    ? 'bg-[#5555AA]/20 font-medium text-white'
                    : 'text-gray-200 hover:bg-white/5 hover:text-white'"
                x-text="label"
            ></button>
        </template>
    </div>
</div>