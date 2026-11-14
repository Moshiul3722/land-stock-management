@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $searching = trim($search) !== '' || $status !== '';
    $badge = fn ($value) => match ($value) {
        'draft' => 'badge-secondary',
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
        default => 'badge-info',
    };
@endphp

{{-- ?create=1 / ?edit=ID only open a modal once; drop them so a refresh does not reopen it --}}
<div x-data
    x-init="if (/[?&](create|edit)=/.test(window.location.search)) { window.history.replaceState({}, '', window.location.pathname); }"
    x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('lot.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h3 class="card-title mb-0 mr-2">{{ __('lot.list_title') }}</h3>
                    <button type="button" class="btn btn-sm btn-success crud-btn" wire:click="openCreate"
                        title="{{ __('lot.manage.add') }}" aria-label="{{ __('lot.manage.add') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ml-1">{{ __('lot.manage.add') }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body position-relative">
                {{-- Toolbar: page size, status filter and search share one row --}}
                <div class="form-row align-items-end mb-3 crud-toolbar">
                    <div class="col-5 col-md-2">
                        <label for="lot_per_page" class="mb-1">{{ __('commontable.per_page') }}</label>
                        <select id="lot_per_page" class="custom-select" wire:model.live="perPage">
                            @foreach ($perPageOptions as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7 col-md-3">
                        <label for="lot_status_filter" class="mb-1">{{ __('lot.table_status') }}</label>
                        <select id="lot_status_filter" class="custom-select" wire:model.live="status">
                            <option value="">{{ __('lot.manage.all_statuses') }}</option>
                            @foreach ($statuses as $option)
                                <option value="{{ $option }}">{{ $statusLabel($option) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-7 mt-2 mt-md-0">
                        <label for="lot_search" class="mb-1">{{ __('commontable.search') }}</label>
                        <input type="search" id="lot_search" class="form-control" autocomplete="off"
                            wire:model.live.debounce.350ms="search"
                            placeholder="{{ __('lot.manage.search_placeholder') }}" title="{{ __('lot.manage.search_placeholder') }}">
                    </div>
                </div>

                <div class="crud-loading" wire:loading.flex wire:target="search, perPage, status, gotoPage, nextPage, previousPage">
                    <span class="badge badge-light p-2"><i class="fas fa-circle-notch fa-spin mr-1" aria-hidden="true"></i>{{ __('commontable.loading') }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table crud-table" id="lots-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ __('lot.table_lot_number') }}</th>
                                <th class="d-none d-lg-table-cell">{{ __('lot.fiscal_year') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('lot.purchase_type') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('lot.table_entry_date') }}</th>
                                <th class="d-none d-xl-table-cell">{{ __('lot.manage.total') }}</th>
                                <th class="d-none d-sm-table-cell">{{ __('lot.table_status') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $lot)
                                <tr wire:key="lot-{{ $lot->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-left crud-wrap">
                                        <span class="font-weight-bold">{{ $lot->lot_number }}</span>
                                        <small class="d-block text-muted">{{ __('lot.manage.item_count', ['count' => $digits($lot->lot_details_count)]) }}</small>
                                        {{-- Narrow screens: hidden columns are summarised under the lot number --}}
                                        <small class="d-block d-md-none text-muted">
                                            {{ collect([$lot->purchaseType->name ?? null, $formatDate($lot->entry_date)])->filter()->join(' · ') }}
                                        </small>
                                        <span class="d-sm-none badge {{ $badge($lot->status) }} mt-1">{{ $statusLabel($lot->status) }}</span>
                                    </td>
                                    <td class="text-center d-none d-lg-table-cell">{{ $lot->fiscalYear ? $digits($lot->fiscalYear->name) : '—' }}</td>
                                    <td class="text-center d-none d-md-table-cell">{{ $lot->purchaseType->name ?? '—' }}</td>
                                    <td class="text-center text-nowrap d-none d-md-table-cell">{{ $formatDate($lot->entry_date) }}</td>
                                    <td class="text-right text-nowrap d-none d-xl-table-cell">{{ (float) $lot->total_value > 0 ? $money($lot->total_value) : '—' }}</td>
                                    <td class="text-center d-none d-sm-table-cell">
                                        <span class="badge {{ $badge($lot->status) }}">{{ $statusLabel($lot->status) }}</span>
                                    </td>
                                    <td class="crud-actions">
                                        <button type="button" class="btn btn-sm btn-info crud-btn" wire:click="openView({{ $lot->id }})"
                                            data-tip="{{ __('lot.manage.view') }}" aria-label="{{ __('lot.manage.view') }}: {{ $lot->lot_number }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                        @if ($lot->status === 'draft')
                                            <button type="button" class="btn btn-sm btn-primary crud-btn" wire:click="openEdit({{ $lot->id }})"
                                                data-tip="{{ __('buttons.edit') }}" aria-label="{{ __('buttons.edit') }}: {{ $lot->lot_number }}">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger crud-btn" wire:click="confirmDelete({{ $lot->id }})"
                                                data-tip="{{ __('buttons.delete') }}" aria-label="{{ __('buttons.delete') }}: {{ $lot->lot_number }}">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr wire:key="lot-empty">
                                    <td colspan="8" class="crud-empty">{{ $searching ? __('commontable.dt_zero_records') : __('commontable.no_record_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.partials.table-footer', ['paginator' => $rows])
            </div>
        </div>
    </div>

    <div class="tw-root">
        <div class="tw-font-sans tw-text-slate-800">

            {{-- ============ Create / edit ============ --}}
            <x-ui.modal wire:model="showForm" max-width="5xl" labelledby="lot-form-title">
                <form wire:submit="save('submit')" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="lot-form-title" class="{{ $ui::MODAL_TITLE }}">{{ $lotId ? __('lot.edit_title') : __('lot.add_title') }}</h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ __('lot.manage.hint') }}</p>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form')
                            <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div>
                        @enderror

                        {{-- Lot details: one row on wide screens --}}
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-[1fr_1.45fr_0.8fr_0.85fr]">
                            <div>
                                <label for="lot-number" class="{{ $ui::LABEL }}">{{ __('lot.lot_number') }} <span class="tw-text-rose-600">*</span></label>
                                <input id="lot-number" type="text" maxlength="191" wire:model="lotNumber" autocomplete="off" autofocus
                                    class="{{ $ui::INPUT }} @error('lotNumber') {{ $ui::INPUT_ERROR }} @enderror"
                                    @error('lotNumber') aria-invalid="true" aria-describedby="lot-number-error" @enderror>
                                @error('lotNumber') <p id="lot-number-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="lot-entry-date" class="{{ $ui::LABEL }}">{{ __('lot.entry_date') }} <span class="tw-text-rose-600">*</span></label>
                                <x-ui.date-field id="lot-entry-date" wire:model="entryDate" :invalid="$errors->has('entryDate')" />
                                @error('entryDate') <p id="lot-entry-date-error" class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="lot-fiscal-year" class="{{ $ui::LABEL }}">{{ __('lot.fiscal_year') }} <span class="tw-text-rose-600">*</span></label>
                                <x-ui.search-select id="lot-fiscal-year" wire:model="fiscalYearId" :clearable="false"
                                    :options="$this->fiscalYears->map(fn ($year) => ['value' => $year->id, 'label' => $digits($year->name)])"
                                    :placeholder="__('lot.manage.select')" :invalid="$errors->has('fiscalYearId')" />
                                @error('fiscalYearId') <p id="lot-fiscal-year-error" class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="lot-purchase-type" class="{{ $ui::LABEL }}">{{ __('lot.purchase_type') }} <span class="tw-text-rose-600">*</span></label>
                                <x-ui.search-select id="lot-purchase-type" wire:model="purchaseTypeId" :clearable="false"
                                    :options="$this->purchaseTypes->map(fn ($type) => ['value' => $type->id, 'label' => $type->name])"
                                    :placeholder="__('lot.manage.select')" :invalid="$errors->has('purchaseTypeId')" />
                                @error('purchaseTypeId') <p id="lot-purchase-type-error" class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Item picker: adds a new line, or changes the line being edited --}}
                        <fieldset class="tw-m-0 tw-rounded-xl tw-border tw-border-solid tw-p-4 {{ $editingIndex !== null ? 'tw-border-brand-200 tw-bg-brand-50/60' : 'tw-border-slate-200 tw-bg-slate-50/70' }}">
                            <legend class="tw-sr-only">{{ $editingIndex !== null ? __('lot.manage.edit_item') : __('lot.manage.add_item') }}</legend>
                            @if ($editingIndex !== null)
                                <p class="tw-mb-3 tw-mt-0 tw-text-sm tw-font-semibold tw-text-brand-700">
                                    <i class="fas fa-pen tw-mr-1" aria-hidden="true"></i>{{ __('lot.manage.editing_item', ['number' => $digits($editingIndex + 1)]) }}
                                </p>
                            @endif
                            <div class="tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-2 lg:tw-grid-cols-[1fr_1.5fr_1fr_9.5rem_auto]">
                                <div>
                                    <label for="lot-pick-category" class="{{ $ui::LABEL }}">{{ __('lot.manage.category') }}</label>
                                    <x-ui.search-select id="lot-pick-category" wire:model.live="pickCategoryId"
                                        :options="$this->categoryOptions->map(fn ($option) => ['value' => $option->id, 'label' => $option->name])"
                                        :placeholder="__('lot.manage.select')" :search-placeholder="__('lot.manage.search_category')"
                                        :invalid="$errors->has('pickCategoryId')" />
                                    @error('pickCategoryId') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="lot-pick-product" class="{{ $ui::LABEL }}">{{ __('lot.table_product') }}</label>
                                    <x-ui.search-select id="lot-pick-product" wire:model="pickProductId" :disabled="! $pickCategoryId"
                                        :options="$this->productOptions->map(fn ($option) => ['value' => $option->id, 'label' => $option->name . ($option->product_unit ? ' (' . $option->unit_label . ')' : '')])"
                                        :placeholder="$pickCategoryId ? __('lot.manage.select') : __('lot.manage.select_category_first')"
                                        :search-placeholder="__('lot.manage.search_product')"
                                        :invalid="$errors->has('pickProductId')" />
                                    @error('pickProductId') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="lot-pick-spec" class="{{ $ui::LABEL }}">{{ __('lot.specification') }}</label>
                                    <input id="lot-pick-spec" type="text" maxlength="191" wire:model="pickSpecification" autocomplete="off"
                                        class="{{ $ui::INPUT }} @error('pickSpecification') {{ $ui::INPUT_ERROR }} @enderror">
                                    @error('pickSpecification') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="lot-pick-quantity" class="{{ $ui::LABEL }}">{{ __('lot.table_quantity') }}</label>
                                    <x-ui.number-field id="lot-pick-quantity" wire:model="pickQuantity" stepper :min="1" :max="1000000" :invalid="$errors->has('pickQuantity')"
                                        x-on:keydown.enter.prevent="$wire.addItem()" />
                                    @error('pickQuantity') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                </div>
                                {{-- Add / update button: in the same row, lined up with the fields --}}
                                <div class="sm:tw-col-span-2 lg:tw-col-span-1">
                                    <span class="{{ $ui::LABEL }} tw-hidden lg:tw-block" aria-hidden="true">&nbsp;</span>
                                    <div class="tw-flex tw-flex-col-reverse tw-gap-2 sm:tw-flex-row sm:tw-justify-end">
                                @if ($editingIndex !== null)
                                    <button type="button" wire:click="cancelItemEdit" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                                @endif
                                <button type="button" wire:click="addItem" wire:loading.attr="disabled" wire:target="addItem"
                                    class="{{ $ui::BTN_SECONDARY }} tw-border-brand-200 tw-bg-brand-50 tw-text-brand-700 hover:tw-bg-brand-100 hover:tw-text-brand-700">
                                    <i class="fas {{ $editingIndex !== null ? 'fa-check' : 'fa-plus' }}" aria-hidden="true" wire:loading.remove wire:target="addItem"></i>
                                    <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="addItem"></i>
                                    {{ $editingIndex !== null ? __('lot.manage.update_item') : __('lot.manage.add_item') }}
                                </button>
                            </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Lines: newest first, ten per page --}}
                        <div>
                            <div class="tw-mb-2 tw-flex tw-items-center tw-justify-between">
                                <h3 class="tw-m-0 tw-text-sm tw-font-semibold tw-text-slate-700">{{ __('lot.manage.items') }}</h3>
                                @if (count($items))
                                    <span class="tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-slate-600">{{ __('lot.manage.item_count', ['count' => $digits(count($items))]) }}</span>
                                @endif
                            </div>

                            @if (count($items))
                                @php $products = $this->itemProducts; @endphp
                                <div class="tw-overflow-x-auto tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 @error('items') tw-border-rose-300 @enderror">
                                    <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-[15px] md:tw-text-base">
                                        <thead>
                                            <tr class="tw-bg-slate-50 tw-text-[13px] tw-font-semibold tw-text-slate-600 md:tw-text-sm">
                                                <th scope="col" class="tw-w-9 tw-px-1 tw-py-2 tw-text-center sm:tw-w-14 sm:tw-whitespace-nowrap sm:tw-px-3">{{ __('lot.table_sl') }}</th>
                                                <th scope="col" class="tw-px-3 tw-py-2">{{ __('lot.manage.item_description') }}</th>
                                                <th scope="col" class="tw-hidden tw-px-3 tw-py-2 sm:tw-table-cell">{{ __('lot.manage.category') }}</th>
                                                <th scope="col" class="tw-whitespace-nowrap tw-px-2 tw-py-2 tw-text-right sm:tw-w-24 sm:tw-px-3">{{ __('lot.table_quantity') }}</th>
                                                <th scope="col" class="tw-w-[4.75rem] tw-whitespace-nowrap tw-px-1 tw-py-2 tw-text-center sm:tw-w-24 sm:tw-px-3">{{ __('commontable.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pageItems as $index => $item)
                                                @php $product = $products->get((int) $item['product_id']); @endphp
                                                <tr wire:key="lot-item-{{ $index }}-{{ $item['product_id'] }}"
                                                    class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-100 {{ $editingIndex === $index ? 'tw-bg-brand-50' : '' }}">
                                                    <td class="tw-px-1 tw-py-0.5 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($index + 1) }}</td>
                                                    <td class="tw-px-3 tw-py-0.5">
                                                        <span class="tw-block tw-break-words tw-font-medium tw-leading-snug tw-text-slate-900">{{ $product->name ?? '—' }}</span>
                                                        @php $itemNote = collect([$item['brand'] ?: null, $item['specification'] ?: null])->filter()->join(' · '); @endphp
                                                        @if ($itemNote !== '')
                                                            <span class="tw-block tw-break-words tw-text-xs tw-leading-snug tw-text-slate-500 md:tw-text-[13px]">{{ $itemNote }}</span>
                                                        @endif
                                                        {{-- Phones: the category column is folded in here --}}
                                                        <span class="tw-block tw-break-words tw-text-xs tw-leading-snug tw-text-slate-500 md:tw-text-[13px] sm:tw-hidden">{{ $product->category->name ?? '' }}</span>
                                                        @error('items.' . $index . '.quantity') <span class="tw-block tw-text-xs tw-font-medium tw-text-rose-600">{{ $message }}</span> @enderror
                                                        @error('items.' . $index . '.product_id') <span class="tw-block tw-text-xs tw-font-medium tw-text-rose-600">{{ $message }}</span> @enderror
                                                    </td>
                                                    <td class="tw-hidden tw-break-words tw-px-3 tw-py-0.5 tw-text-slate-700 sm:tw-table-cell">{{ $product->category->name ?? '—' }}</td>
                                                    <td class="tw-whitespace-nowrap tw-px-2 tw-py-0.5 tw-text-right tw-font-semibold tw-tabular-nums tw-text-slate-900 sm:tw-px-3">
                                                        {{ $digits($item['quantity']) }}
                                                        @if ($product && $product->product_unit && ! is_numeric($product->product_unit))
                                                            <span class="tw-block tw-text-xs tw-font-normal tw-text-slate-500 sm:tw-inline md:tw-text-[13px]">{{ $product->unit_label }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="tw-whitespace-nowrap tw-px-0 tw-py-0 tw-text-center sm:tw-px-2">
                                                        <button type="button" wire:click="editItem({{ $index }})"
                                                            class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-md tw-border-0 tw-bg-transparent tw-text-slate-500 sm:tw-h-7 sm:tw-w-7 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40"
                                                            aria-label="{{ __('buttons.edit') }}: {{ $product->name ?? '' }}" title="{{ __('buttons.edit') }}">
                                                            <i class="fas fa-pen tw-text-xs" aria-hidden="true"></i>
                                                        </button>
                                                        <button type="button" wire:click="removeItem({{ $index }})"
                                                            wire:confirm="{{ __('lot.manage.remove_item_confirm') }}"
                                                            class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-md tw-border-0 tw-bg-transparent tw-text-slate-500 sm:tw-h-7 sm:tw-w-7 tw-transition hover:tw-bg-rose-50 hover:tw-text-rose-600 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-rose-500/40"
                                                            aria-label="{{ __('lot.manage.remove_item') }}: {{ $product->name ?? '' }}" title="{{ __('lot.manage.remove_item') }}">
                                                            <i class="fas fa-trash-alt tw-text-xs" aria-hidden="true"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($itemPages > 1)
                                    <nav class="tw-mt-2 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2" aria-label="{{ __('commontable.pagination') }}">
                                        <p class="tw-m-0 tw-text-xs tw-text-slate-500">
                                            {{ strtr(__('commontable.dt_info'), ['_TOTAL_' => $digits(count($items)), '_START_' => $digits(($itemPage - 1) * 10 + 1), '_END_' => $digits(min($itemPage * 10, count($items)))]) }}
                                        </p>
                                        <div class="tw-flex tw-flex-wrap tw-gap-1">
                                            @for ($page = 1; $page <= $itemPages; $page++)
                                                <button type="button" wire:click="gotoItemPage({{ $page }})" wire:key="lot-item-page-{{ $page }}"
                                                    @if ($page === $itemPage) aria-current="page" @endif
                                                    class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-px-2 tw-text-xs tw-font-semibold tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 {{ $page === $itemPage ? 'tw-border-brand-600 tw-bg-brand-600 tw-text-white' : 'tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50' }}">
                                                    {{ $digits($page) }}
                                                </button>
                                            @endfor
                                        </div>
                                    </nav>
                                @endif
                            @else
                                <div class="tw-rounded-xl tw-border tw-border-dashed tw-border-slate-300 tw-px-4 tw-py-8 tw-text-center tw-text-sm tw-text-slate-500 @error('items') tw-border-rose-300 tw-bg-rose-50/40 @enderror">
                                    <i class="fas fa-box-open tw-mb-2 tw-block tw-text-xl tw-text-slate-300" aria-hidden="true"></i>
                                    {{ __('lot.manage.no_items') }}
                                </div>
                            @endif
                            @error('items') <p class="{{ $ui::ERROR }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="lot-remarks" class="{{ $ui::LABEL }}">{{ __('lot.remarks') }}</label>
                            <textarea id="lot-remarks" rows="2" maxlength="191" wire:model="remarks"
                                class="{{ $ui::INPUT }} tw-resize-y @error('remarks') {{ $ui::INPUT_ERROR }} @enderror"></textarea>
                            @error('remarks') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="ui-modal-foot tw-flex tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-3 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between sm:tw-px-6">
                        <p class="tw-m-0 tw-hidden tw-text-xs tw-text-slate-500 lg:tw-block">{{ __('lot.manage.submit_hint') }}</p>
                        <div class="tw-flex tw-flex-col-reverse tw-gap-2 sm:tw-ml-auto sm:tw-flex-row">
                            <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                            <button type="button" wire:click="save('save_draft')" wire:loading.attr="disabled" wire:target="save" class="{{ $ui::BTN_SECONDARY }}">
                                <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save('save_draft')"></i>
                                <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="save('save_draft')"></i>
                                {{ __('buttons.save_draft') }}
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="{{ $ui::BTN_PRIMARY }}">
                                <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save('submit')"></i>
                                <i class="fas fa-paper-plane" aria-hidden="true" wire:loading.remove wire:target="save('submit')"></i>
                                {{ __('buttons.submit_for_approval') }}
                            </button>
                        </div>
                    </div>
                </form>
            </x-ui.modal>

            {{-- ============ View (read-only) ============ --}}
            <x-ui.modal wire:model="showView" max-width="4xl" labelledby="lot-view-title">
                @if ($viewed = $this->viewing)
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-flex tw-min-w-0 tw-flex-1 tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1">
                            <h2 id="lot-view-title" class="{{ $ui::MODAL_TITLE }} tw-break-words">{{ $viewed->lot_number }}</h2>
                            <span class="badge {{ $badge($viewed->status) }}">{{ $statusLabel($viewed->status) }}</span>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="{{ $ui::MODAL_BODY }}">
                        <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-3">
                            <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                                <dt class="tw-text-xs tw-font-medium tw-text-slate-500">{{ __('lot.table_entry_date') }}</dt>
                                <dd class="tw-m-0 tw-mt-0.5 tw-font-semibold tw-text-slate-900">{{ $formatDate($viewed->entry_date) }}</dd>
                            </div>
                            <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                                <dt class="tw-text-xs tw-font-medium tw-text-slate-500">{{ __('lot.fiscal_year') }}</dt>
                                <dd class="tw-m-0 tw-mt-0.5 tw-font-semibold tw-text-slate-900">{{ $viewed->fiscalYear ? $digits($viewed->fiscalYear->name) : '—' }}</dd>
                            </div>
                            <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                                <dt class="tw-text-xs tw-font-medium tw-text-slate-500">{{ __('lot.purchase_type') }}</dt>
                                <dd class="tw-m-0 tw-mt-0.5 tw-font-semibold tw-text-slate-900">{{ $viewed->purchaseType->name ?? '—' }}</dd>
                            </div>
                        </dl>

                        @php
                            // Prices and brands are only shown for lots that actually carry them (older entries).
                            $hasPrices = $viewed->lotDetails->contains(fn ($detail) => (float) $detail->unit_price > 0);
                            $hasBrands = $viewed->lotDetails->contains(fn ($detail) => filled($detail->brand));
                            $grand = 0;
                            // More than 10 items: shown 10 per page (the total below still covers every item)
                            $viewCount = $viewed->lotDetails->count();
                            $viewPages = (int) ceil($viewCount / 10);
                        @endphp
                        <div x-data="{ page: 1 }" wire:key="lot-view-items-{{ $viewed->id }}">
                        <div class="tw-overflow-x-auto tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                            <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-[15px] md:tw-text-base {{ $hasPrices ? 'tw-min-w-[560px]' : '' }}">
                                <thead>
                                    <tr class="tw-bg-slate-50 tw-text-[13px] tw-font-semibold tw-text-slate-600 md:tw-text-sm">
                                        <th scope="col" class="tw-w-9 tw-px-1 tw-py-2 tw-text-center sm:tw-w-14 sm:tw-whitespace-nowrap sm:tw-px-3">{{ __('lot.table_sl') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2">{{ __('lot.manage.item_description') }}</th>
                                        <th scope="col" class="tw-hidden tw-px-3 tw-py-2 sm:tw-table-cell">{{ __('lot.manage.category') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 tw-text-right sm:tw-whitespace-nowrap">{{ __('lot.manage.stored_quantity') }}</th>
                                        @if ($hasPrices)
                                            <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-right">{{ __('lot.manage.unit_price') }}</th>
                                            <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-right">{{ __('lot.manage.line_total') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewed->lotDetails as $detail)
                                        @php $line = (float) $detail->quantity * (float) $detail->unit_price; $grand += $line; @endphp
                                        <tr wire:key="lot-view-item-{{ $detail->id }}" class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-100"
                                            @if ($viewPages > 1) x-show="page === {{ intdiv($loop->index, 10) + 1 }}" @endif>
                                            <td class="tw-px-1 tw-py-0.5 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($loop->iteration) }}</td>
                                            <td class="tw-px-3 tw-py-0.5">
                                                <span class="tw-block tw-break-words tw-font-medium tw-leading-snug tw-text-slate-900">{{ $detail->product->name ?? '—' }}</span>
                                                @php $detailNote = collect([$hasBrands ? $detail->brand : null, $detail->specification])->filter()->join(' · '); @endphp
                                                @if ($detailNote !== '')
                                                    <span class="tw-block tw-break-words tw-text-xs tw-leading-snug tw-text-slate-500 md:tw-text-[13px]">{{ $detailNote }}</span>
                                                @endif
                                                <span class="tw-block tw-break-words tw-text-xs tw-leading-snug tw-text-slate-500 md:tw-text-[13px] sm:tw-hidden">{{ $detail->product->category->name ?? '' }}</span>
                                            </td>
                                            <td class="tw-hidden tw-break-words tw-px-3 tw-py-0.5 tw-text-slate-700 sm:tw-table-cell">{{ $detail->product->category->name ?? '—' }}</td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-0.5 tw-text-right tw-font-semibold tw-tabular-nums tw-text-slate-900">
                                                {{ $digits($detail->quantity) }}
                                                @if ($detail->product && $detail->product->product_unit && ! is_numeric($detail->product->product_unit))
                                                    <span class="tw-font-normal tw-text-slate-600">{{ $detail->product->unit_label }}</span>
                                                @endif
                                            </td>
                                            @if ($hasPrices)
                                                <td class="tw-whitespace-nowrap tw-px-3 tw-py-0.5 tw-text-right tw-tabular-nums">{{ $money($detail->unit_price) }}</td>
                                                <td class="tw-whitespace-nowrap tw-px-3 tw-py-0.5 tw-text-right tw-font-semibold tw-tabular-nums tw-text-slate-900">{{ $money($line) }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                                @if ($hasPrices)
                                    <tfoot>
                                        <tr class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50">
                                            <th scope="row" colspan="5" class="tw-px-3 tw-py-2 tw-text-right tw-font-semibold tw-text-slate-700">{{ __('lot.manage.total') }}</th>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-right tw-font-bold tw-tabular-nums tw-text-slate-900">{{ $money($grand) }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                        @if ($viewPages > 1)
                            <nav class="tw-mt-2 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2" aria-label="{{ __('commontable.pagination') }}">
                                <p class="tw-m-0 tw-text-xs tw-text-slate-500">
                                    @for ($page = 1; $page <= $viewPages; $page++)
                                        <span x-show="page === {{ $page }}" @if ($page > 1) x-cloak @endif>{{ strtr(__('commontable.dt_info'), ['_TOTAL_' => $digits($viewCount), '_START_' => $digits(($page - 1) * 10 + 1), '_END_' => $digits(min($page * 10, $viewCount))]) }}</span>
                                    @endfor
                                </p>
                                <div class="tw-flex tw-flex-wrap tw-gap-1">
                                    @for ($page = 1; $page <= $viewPages; $page++)
                                        <button type="button" x-on:click="page = {{ $page }}" wire:key="lot-view-page-{{ $viewed->id }}-{{ $page }}"
                                            x-bind:aria-current="page === {{ $page }} ? 'page' : null"
                                            x-bind:class="page === {{ $page }} ? 'tw-border-brand-600 tw-bg-brand-600 tw-text-white' : 'tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50'"
                                            class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-px-2 tw-text-xs tw-font-semibold tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40">
                                            {{ $digits($page) }}
                                        </button>
                                    @endfor
                                </div>
                            </nav>
                        @endif
                        </div>

                        @if ($viewed->remarks)
                            <div>
                                <h3 class="tw-mb-1 tw-mt-0 tw-text-sm tw-font-semibold tw-text-slate-700">{{ __('lot.remarks') }}</h3>
                                <p class="tw-m-0 tw-break-words tw-rounded-lg tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-sm tw-text-slate-700">{{ $viewed->remarks }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('commontable.close') }}</button>
                        {{-- The lot on an A4 sheet in a new tab: a preview first, or straight to the printer --}}
                        <a href="{{ route('lots.print', ['lot' => $viewed, 'preview' => 1]) }}" target="_blank" rel="noopener" class="{{ $ui::BTN_SECONDARY }}">
                            <i class="fas fa-eye" aria-hidden="true"></i> {{ __('lot.manage.print_preview') }}
                        </a>
                        <a href="{{ route('lots.print', $viewed) }}" target="_blank" rel="noopener" class="{{ $ui::BTN_SECONDARY }}">
                            <i class="fas fa-print" aria-hidden="true"></i> {{ __('lot.manage.print') }}
                        </a>
                        @if ($viewed->status === 'draft')
                            <button type="button" wire:click="confirmDelete({{ $viewed->id }})" class="{{ $ui::BTN_SECONDARY }} tw-text-rose-600 hover:tw-text-rose-700">
                                <i class="fas fa-trash-alt" aria-hidden="true"></i> {{ __('buttons.delete') }}
                            </button>
                            <button type="button" wire:click="openEdit({{ $viewed->id }})" class="{{ $ui::BTN_SECONDARY }}">
                                <i class="fas fa-pen" aria-hidden="true"></i> {{ __('buttons.edit') }}
                            </button>
                            <button type="button" wire:click="submitDraft" wire:loading.attr="disabled" wire:target="submitDraft"
                                wire:confirm="{{ __('lot.manage.submit_confirm') }}" class="{{ $ui::BTN_PRIMARY }}">
                                <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="submitDraft"></i>
                                <i class="fas fa-paper-plane" aria-hidden="true" wire:loading.remove wire:target="submitDraft"></i>
                                {{ __('buttons.submit_for_approval') }}
                            </button>
                        @endif
                    </div>
                @endif
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="lot-delete"
                :title="__('lot.manage.delete_title')" :text="__('lot.manage.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
