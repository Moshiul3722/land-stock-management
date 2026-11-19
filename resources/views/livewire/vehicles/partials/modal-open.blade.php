{{-- Start of the add / edit modal (header). Expects $t, $editingId, $titleId. Close with modal-close. --}}
@php $ui = \App\Livewire\Concerns\Ui::class; @endphp
<div class="{{ $ui::MODAL_HEADER }}">
    <div class="tw-min-w-0 tw-flex-1">
        <h2 id="{{ $titleId }}" class="{{ $ui::MODAL_TITLE }}">{{ $editingId ? $t('edit') : $t('add') }}</h2>
        <p class="{{ $ui::MODAL_HINT }}">{{ $t('hint') }}</p>
    </div>
    <x-ui.modal-today />
    <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
        <i class="fas fa-times" aria-hidden="true"></i>
    </button>
</div>
