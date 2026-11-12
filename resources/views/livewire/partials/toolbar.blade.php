{{-- Page size + search for a Livewire CRUD list. Expects $perPageOptions, $digits, $placeholder, $id. --}}
<div class="form-row align-items-end mb-3 crud-toolbar">
    <div class="col-5 col-md-3 col-lg-2">
        <label for="{{ $id }}_per_page" class="mb-1">{{ __('commontable.per_page') }}</label>
        <select id="{{ $id }}_per_page" class="custom-select" wire:model.live="perPage">
            @foreach ($perPageOptions as $size)
                <option value="{{ $size }}">{{ $digits($size) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-9 col-lg-10 mt-2 mt-md-0">
        <label for="{{ $id }}_search" class="mb-1">{{ __('commontable.search') }}</label>
        <input type="search" id="{{ $id }}_search" class="form-control" autocomplete="off"
            wire:model.live.debounce.350ms="search" placeholder="{{ $placeholder }}" title="{{ $placeholder }}">
    </div>
</div>
