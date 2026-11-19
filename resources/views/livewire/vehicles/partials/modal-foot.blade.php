{{-- Footer of the add / edit modal: cancel + save. Expects $editingId. --}}
@php $ui = \App\Livewire\Concerns\Ui::class; @endphp
<div class="{{ $ui::MODAL_FOOTER }}">
    <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="{{ $ui::BTN_PRIMARY }}">
        <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save"></i>
        <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="save"></i>
        {{ $editingId ? __('buttons.update') : __('buttons.save') }}
    </button>
</div>
