{{--
    Accessible modal dialog for Livewire screens.

    Usage: <x-ui.modal wire:model="showForm" max-width="3xl" labelledby="form-title"> ... </x-ui.modal>

    - Open state is entangled with the Livewire property given in wire:model.
    - Focus is trapped inside while open and restored to the trigger on close (Alpine x-trap).
    - Escape, the close button and a backdrop click close it; page scroll is locked.
    - Phones get a bottom sheet; larger screens get a centred dialog.
    Must be rendered inside a `.tw-root` element (Tailwind scope).
--}}
@props([
    'maxWidth' => '2xl',
    'labelledby' => null,
    'describedby' => null,
])

@php
    $widths = [
        'md' => 'sm:tw-max-w-md',
        'lg' => 'sm:tw-max-w-lg',
        '2xl' => 'sm:tw-max-w-2xl',
        '3xl' => 'sm:tw-max-w-2xl md:tw-max-w-3xl',
        '4xl' => 'sm:tw-max-w-2xl md:tw-max-w-3xl lg:tw-max-w-4xl',
        '5xl' => 'sm:tw-max-w-2xl md:tw-max-w-3xl lg:tw-max-w-5xl',
    ];
    $width = $widths[$maxWidth] ?? $widths['2xl'];
@endphp

<div
    x-data="{ open: @entangle($attributes->wire('model')) }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="if (open) open = false"
    class="tw-fixed tw-inset-0 tw-z-[1060] tw-flex tw-items-end tw-justify-center sm:tw-items-center sm:tw-p-6"
    role="dialog"
    aria-modal="true"
    @if ($labelledby) aria-labelledby="{{ $labelledby }}" @endif
    @if ($describedby) aria-describedby="{{ $describedby }}" @endif
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="tw-transition tw-ease-out tw-duration-200"
        x-transition:enter-start="tw-opacity-0"
        x-transition:enter-end="tw-opacity-100"
        x-transition:leave="tw-transition tw-ease-in tw-duration-150"
        x-transition:leave-start="tw-opacity-100"
        x-transition:leave-end="tw-opacity-0"
        x-on:click="open = false"
        class="tw-fixed tw-inset-0 tw-bg-slate-900/45 tw-backdrop-blur-[2px]"
        aria-hidden="true"
    ></div>

    {{-- Panel --}}
    <div
        x-show="open"
        x-trap.noscroll="open"
        x-transition:enter="tw-transition tw-ease-out tw-duration-200"
        x-transition:enter-start="tw-opacity-0 tw-translate-y-4 sm:tw-translate-y-0 sm:tw-scale-[0.98]"
        x-transition:enter-end="tw-opacity-100 tw-translate-y-0 sm:tw-scale-100"
        x-transition:leave="tw-transition tw-ease-in tw-duration-150"
        x-transition:leave-start="tw-opacity-100 tw-translate-y-0 sm:tw-scale-100"
        x-transition:leave-end="tw-opacity-0 tw-translate-y-4 sm:tw-translate-y-0 sm:tw-scale-[0.98]"
        {{ $attributes->whereDoesntStartWith('wire:model')->merge([
            'class' => "tw-relative tw-flex tw-max-h-[92vh] tw-w-full {$width} tw-flex-col tw-overflow-hidden tw-rounded-t-2xl tw-bg-white tw-shadow-dialog tw-ring-1 tw-ring-slate-900/10 sm:tw-max-h-[88vh] sm:tw-rounded-2xl",
        ]) }}
    >
        {{ $slot }}
    </div>
</div>
