{{--
    Delete confirmation dialog for Livewire CRUD screens (Tailwind, inside .tw-root).
    Usage: <x-ui.confirm-delete wire:model="showDelete" action="delete" :title="..." :text="..." :name="$label" id="x" />
--}}
@props(['action' => 'delete', 'title', 'text', 'name' => '', 'id' => 'confirm-delete'])

@php
    $ui = \App\Livewire\Concerns\Ui::class;
@endphp

<x-ui.modal wire:model="{{ $attributes->wire('model')->value() }}" max-width="md" labelledby="{{ $id }}-title" describedby="{{ $id }}-text">
    <div class="tw-px-5 tw-pb-4 tw-pt-6 tw-text-center sm:tw-px-6">
        <div class="tw-mx-auto tw-mb-4 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-rose-50 tw-text-lg tw-text-rose-600" aria-hidden="true">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h2 id="{{ $id }}-title" class="{{ $ui::MODAL_TITLE }}">{{ $title }}</h2>
        <p id="{{ $id }}-text" class="tw-mb-0 tw-mt-2 tw-text-sm tw-text-slate-600">{{ $text }}</p>
        @if ($name !== '')
            <p class="tw-mb-0 tw-mt-2 tw-break-words tw-text-sm tw-font-semibold tw-text-slate-900">{{ $name }}</p>
        @endif
    </div>
    <div class="ui-modal-foot tw-flex tw-flex-col-reverse tw-gap-2 tw-px-5 tw-pb-5 sm:tw-flex-row sm:tw-justify-center sm:tw-px-6">
        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }} sm:tw-min-w-[8rem]">{{ __('sweetalert.cancel') }}</button>
        <button type="button" wire:click="{{ $action }}" wire:loading.attr="disabled" wire:target="{{ $action }}" class="{{ $ui::BTN_DANGER }} sm:tw-min-w-[8rem]">
            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="{{ $action }}"></i>
            <i class="fas fa-trash-alt" aria-hidden="true" wire:loading.remove wire:target="{{ $action }}"></i>
            {{ __('sweetalert.delete_notice') }}
        </button>
    </div>
</x-ui.modal>
