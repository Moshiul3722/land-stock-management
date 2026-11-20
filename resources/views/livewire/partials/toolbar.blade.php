{{--
    Page size + search for a Livewire CRUD list. Expects $perPageOptions, $digits, $placeholder, $id.
    Optional $dateFilter = ['model' => 'property', 'label' => '...']: a date field beside the
    search box (the property holds YYYY-MM-DD, '' = any date) and a button that clears the
    search and the date (the component's clearFilters()). All fields are then the same height.
--}}
@php
    $withDate = ! empty($dateFilter);
    $fieldHeight = $withDate ? 'height: 2.75rem;' : '';
@endphp
<div class="form-row align-items-end mb-3 crud-toolbar">
    <div class="col-5 {{ $withDate ? 'col-md-3 col-lg-2' : 'col-md-3 col-lg-2' }}">
        <label for="{{ $id }}_per_page" class="mb-1">{{ __('commontable.per_page') }}</label>
        <select id="{{ $id }}_per_page" class="custom-select" wire:model.live="perPage" @if ($withDate) style="{{ $fieldHeight }}" @endif>
            @foreach ($perPageOptions as $size)
                <option value="{{ $size }}">{{ $digits($size) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 {{ $withDate ? 'col-md-9 col-lg-4 col-xl-5' : 'col-md-9 col-lg-10' }} mt-2 mt-md-0">
        <label for="{{ $id }}_search" class="mb-1">{{ __('commontable.search') }}</label>
        <input type="search" id="{{ $id }}_search" class="form-control" autocomplete="off" @if ($withDate) style="{{ $fieldHeight }}" @endif
            wire:model.live.debounce.350ms="search" placeholder="{{ $placeholder }}" title="{{ $placeholder }}">
    </div>
    @if ($withDate)
        <div class="col-7 col-lg-3 mt-2 mt-lg-0">
            <label for="{{ $id }}_date" class="mb-1">{{ $dateFilter['label'] }}</label>
            <div class="tw-root"><div class="tw-font-sans">
                <x-ui.date-field :id="$id . '_date'" wire:model.live="{{ $dateFilter['model'] }}" compact clearable :placeholder="__('commontable.any_date')" />
            </div></div>
        </div>
        <div class="col-5 col-lg-3 col-xl-2 mt-2 mt-lg-0">
            <button type="button" class="btn btn-outline-secondary btn-block d-inline-flex align-items-center justify-content-center px-2" style="{{ $fieldHeight }}"
                wire:click="clearFilters" wire:loading.attr="disabled" wire:target="clearFilters"
                @disabled(trim((string) ($this->search ?? '')) === '' && (string) data_get($this, $dateFilter['model']) === '')
                title="{{ __('commontable.clear_filters') }}" aria-label="{{ __('commontable.clear_filters') }}">
                <i class="fas fa-times-circle mr-1" aria-hidden="true"></i><span class="text-truncate">{{ __('commontable.clear_filters') }}</span>
            </button>
        </div>
    @endif
</div>
