@php
    $label = 'tw-mb-1.5 tw-block tw-text-[13px] tw-font-semibold tw-text-slate-600';
    $input = 'tw-block tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition placeholder:tw-text-slate-400 hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15 disabled:tw-cursor-not-allowed disabled:tw-bg-slate-100 disabled:tw-text-slate-400';
    $inputError = 'tw-border-rose-400 focus:tw-border-rose-500 focus:tw-ring-rose-500/15';
    $btn = 'tw-inline-flex tw-min-h-11 tw-select-none tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-px-4 tw-text-sm tw-font-semibold tw-leading-none tw-transition hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 active:tw-translate-y-px disabled:tw-cursor-not-allowed disabled:tw-opacity-60';
    $btnPrimary = $btn . ' tw-border-brand-600 tw-bg-brand-600 tw-text-white tw-shadow-sm hover:tw-border-brand-700 hover:tw-bg-brand-700 hover:tw-text-white focus-visible:tw-ring-brand-500/30';
    $btnSecondary = $btn . ' tw-border-slate-300 tw-bg-white tw-text-slate-700 tw-shadow-sm hover:tw-bg-slate-50 hover:tw-text-slate-900 focus-visible:tw-ring-slate-400/30';
    $btnDanger = $btn . ' tw-border-rose-600 tw-bg-rose-600 tw-text-white tw-shadow-sm hover:tw-border-rose-700 hover:tw-bg-rose-700 hover:tw-text-white focus-visible:tw-ring-rose-500/30';
    $iconBtn = 'tw-inline-flex tw-h-11 tw-w-11 tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-border-transparent tw-bg-transparent tw-text-slate-500 tw-transition hover:tw-bg-slate-100 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 md:tw-h-9 md:tw-w-9';
    $card = 'tw-rounded-2xl tw-border tw-border-solid tw-border-slate-200/80 tw-bg-white tw-shadow-surface';
    $errorText = 'tw-mt-1.5 tw-mb-0 tw-flex tw-items-start tw-gap-1.5 tw-text-[13px] tw-font-medium tw-text-rose-600';
    $badge = fn ($status) => 'tw-inline-flex tw-items-center tw-gap-1.5 tw-whitespace-nowrap tw-rounded-full tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-ring-1 tw-ring-inset ' . match ($status) {
        'draft' => 'tw-bg-slate-100 tw-text-slate-700 tw-ring-slate-300',
        'pending' => 'tw-bg-amber-50 tw-text-amber-800 tw-ring-amber-300',
        'approved', 'completed' => 'tw-bg-emerald-50 tw-text-emerald-800 tw-ring-emerald-300',
        'processed', 'delivered' => 'tw-bg-sky-50 tw-text-sky-800 tw-ring-sky-300',
        'rejected' => 'tw-bg-rose-50 tw-text-rose-800 tw-ring-rose-300',
        default => 'tw-bg-slate-100 tw-text-slate-700 tw-ring-slate-300',
    };
    $dot = fn ($status) => 'tw-h-1.5 tw-w-1.5 tw-rounded-full ' . match ($status) {
        'pending' => 'tw-bg-amber-500',
        'approved', 'completed' => 'tw-bg-emerald-500',
        'processed', 'delivered' => 'tw-bg-sky-500',
        'rejected' => 'tw-bg-rose-500',
        default => 'tw-bg-slate-400',
    };
    $rows = $this->requisitions;
@endphp

<div
    class="tw-root"
    x-data
    x-on:requisition-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)"
>
    <div class="tw-space-y-4 tw-pb-6 tw-pt-3 tw-font-sans tw-text-slate-800 sm:tw-space-y-5">

        {{-- ============ Page header ============ --}}
        <header class="{{ $card }} tw-relative tw-overflow-hidden tw-px-4 tw-py-4 sm:tw-px-6 sm:tw-py-5">
            <div class="tw-absolute tw-inset-x-0 tw-top-0 tw-h-1 tw-bg-gradient-to-r tw-from-brand-600 tw-via-brand-500 tw-to-accent-500" aria-hidden="true"></div>
            <div class="tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between">
                <div class="tw-min-w-0">
                    <nav aria-label="breadcrumb" class="tw-mb-1 tw-text-xs tw-text-slate-500">
                        <a href="{{ route('home') }}" class="tw-text-slate-500 hover:tw-text-brand-600">{{ __('breadcrumb.home') }}</a>
                        <span class="tw-mx-1.5 tw-text-slate-300" aria-hidden="true">/</span>
                        <span aria-current="page">{{ __('requisition.manage.title') }}</span>
                    </nav>
                    <h1 class="tw-m-0 tw-text-xl tw-font-bold tw-leading-snug tw-tracking-tight tw-text-slate-900 sm:tw-text-2xl">
                        {{ __('requisition.manage.title') }}
                    </h1>
                    <p class="tw-mb-0 tw-mt-1 tw-text-sm tw-text-slate-500">{{ __('requisition.manage.subtitle') }}</p>
                </div>

                @if ($canCreate)
                    <button type="button" wire:click="openCreate" wire:loading.attr="disabled" wire:target="openCreate"
                        class="{{ $btnPrimary }} tw-w-full sm:tw-w-auto">
                        <i class="fas fa-plus" aria-hidden="true" wire:loading.remove wire:target="openCreate"></i>
                        <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="openCreate"></i>
                        {{ __('requisition.manage.new') }}
                    </button>
                @endif
            </div>
        </header>

        {{-- ============ Filters ============ --}}
        <section class="{{ $card }} tw-p-4 sm:tw-p-5" x-data="{ more: @js($hasFilters && ($status || $dateFrom || $dateTo)) }"
            aria-label="{{ __('requisition.manage.filters') }}">
            <div class="tw-flex tw-gap-2">
                <div class="tw-relative tw-flex-1">
                    <label for="req-search" class="tw-sr-only">{{ __('requisition.manage.search') }}</label>
                    <i class="fas fa-search tw-pointer-events-none tw-absolute tw-left-3.5 tw-top-1/2 tw--translate-y-1/2 tw-text-sm tw-text-slate-400" aria-hidden="true"></i>
                    <input id="req-search" type="search" wire:model.live.debounce.400ms="search" autocomplete="off"
                        placeholder="{{ __('requisition.manage.search_placeholder') }}"
                        class="{{ $input }} tw-pl-10">
                </div>
                <button type="button" x-on:click="more = !more" x-bind:aria-expanded="more.toString()" aria-controls="req-more-filters"
                    class="{{ $btnSecondary }} tw-px-3 md:tw-hidden">
                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                    <span class="tw-sr-only sm:tw-not-sr-only">{{ __('requisition.manage.filters') }}</span>
                </button>
            </div>

            <div id="req-more-filters" class="tw-mt-3 tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-2 md:tw-grid-cols-4 lg:tw-grid-cols-[1fr_1fr_1fr_auto_auto] lg:tw-items-end"
                x-bind:class="more ? 'tw-grid' : 'tw-hidden md:tw-grid'">
                <div>
                    <label for="req-status" class="{{ $label }}">{{ __('requisition.manage.status_filter') }}</label>
                    <select id="req-status" wire:model.live="status" class="{{ $input }}">
                        <option value="">{{ __('requisition.manage.all_statuses') }}</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}">{{ $statusLabel($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="req-from" class="{{ $label }}">{{ __('requisition.manage.date_from') }}</label>
                    <input id="req-from" type="date" wire:model.live="dateFrom" max="{{ $dateTo ?: '' }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="req-to" class="{{ $label }}">{{ __('requisition.manage.date_to') }}</label>
                    <input id="req-to" type="date" wire:model.live="dateTo" min="{{ $dateFrom ?: '' }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="req-per-page" class="{{ $label }}">{{ __('requisition.manage.per_page') }}</label>
                    <select id="req-per-page" wire:model.live="perPage" class="{{ $input }} lg:tw-w-24">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $digits($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:tw-col-span-2 md:tw-col-span-4 lg:tw-col-span-1">
                    <button type="button" wire:click="resetFilters" @disabled(! $hasFilters)
                        class="{{ $btnSecondary }} tw-w-full lg:tw-w-auto">
                        <i class="fas fa-times" aria-hidden="true"></i>
                        {{ __('requisition.manage.clear_filters') }}
                    </button>
                </div>
            </div>
        </section>

        {{-- ============ List ============ --}}
        <section class="{{ $card }} tw-relative tw-overflow-hidden" aria-labelledby="req-list-title" aria-live="polite">
            <h2 id="req-list-title" class="tw-sr-only">{{ __('requisition.my_title') }}</h2>

            {{-- Loading overlay while the list refreshes --}}
            <div wire:loading.delay.flex wire:target="search, status, dateFrom, dateTo, perPage, resetFilters, gotoPage, nextPage, previousPage, delete, save, submitDraft"
                class="tw-absolute tw-inset-0 tw-z-10 tw-hidden tw-items-center tw-justify-center tw-bg-white/70 tw-backdrop-blur-[1px]">
                <span class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-full tw-bg-white tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-slate-600 tw-shadow-surface">
                    <i class="fas fa-circle-notch fa-spin tw-text-brand-600" aria-hidden="true"></i>
                    {{ __('requisition.manage.loading') }}
                </span>
            </div>

            @if ($rows->count())
                {{-- Desktop / tablet landscape: table --}}
                <div class="tw-hidden tw-overflow-x-auto md:tw-block">
                    <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-sm">
                        <thead>
                            <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-slate-500">
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-3">{{ __('requisition.manage.requisition_no') }}</th>
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-3">{{ __('requisition.manage.date') }}</th>
                                <th scope="col" class="tw-px-5 tw-py-3">{{ __('requisition.manage.items') }}</th>
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-3">{{ __('requisition.manage.status_label') }}</th>
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-3 tw-text-right">{{ __('requisition.manage.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $req)
                                <tr wire:key="req-row-{{ $req->id }}" class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-transition hover:tw-bg-slate-50/70 last:tw-border-b-0">
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-3.5 tw-font-semibold tw-tabular-nums tw-text-slate-900">#{{ $digits($req->id) }}</td>
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-3.5 tw-text-slate-600">{{ $formatDate($req->requisition_date) }}</td>
                                    <td class="tw-max-w-md tw-px-5 tw-py-3.5">
                                        <span class="tw-font-medium tw-text-slate-800">{{ __('requisition.manage.items_summary', ['count' => $digits($req->items_count)]) }}</span>
                                        <span class="tw-block tw-truncate tw-text-xs tw-text-slate-500" title="{{ $req->items->pluck('product.name')->filter()->join(', ') }}">
                                            {{ $req->items->pluck('product.name')->filter()->take(3)->join(', ') }}{{ $req->items_count > 3 ? ' …' : '' }}
                                        </span>
                                    </td>
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-3.5">
                                        <span class="{{ $badge($req->status) }}"><span class="{{ $dot($req->status) }}" aria-hidden="true"></span>{{ $statusLabel($req->status) }}</span>
                                    </td>
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-2 tw-text-right">
                                        <div class="tw-inline-flex tw-items-center tw-gap-0.5">
                                            <button type="button" wire:click="openView({{ $req->id }})" class="{{ $iconBtn }}"
                                                title="{{ __('requisition.manage.view') }}" aria-label="{{ __('requisition.manage.view') }} #{{ $req->id }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </button>
                                            @if ($canEdit($req))
                                                <button type="button" wire:click="openEdit({{ $req->id }})" class="{{ $iconBtn }} hover:tw-text-brand-600"
                                                    title="{{ __('requisition.manage.edit') }}" aria-label="{{ __('requisition.manage.edit') }} #{{ $req->id }}">
                                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" wire:click="confirmDelete({{ $req->id }})" class="{{ $iconBtn }} hover:tw-bg-rose-50 hover:tw-text-rose-600"
                                                    title="{{ __('requisition.manage.delete') }}" aria-label="{{ __('requisition.manage.delete') }} #{{ $req->id }}">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Phones / tablet portrait: cards --}}
                <ul class="tw-m-0 tw-list-none tw-divide-y tw-divide-slate-100 tw-p-0 md:tw-hidden">
                    @foreach ($rows as $req)
                        <li wire:key="req-card-{{ $req->id }}" class="tw-px-4 tw-py-4">
                            <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                                <div class="tw-min-w-0">
                                    <p class="tw-m-0 tw-font-semibold tw-tabular-nums tw-text-slate-900">#{{ $digits($req->id) }}</p>
                                    <p class="tw-m-0 tw-text-xs tw-text-slate-500">{{ $formatDate($req->requisition_date) }}</p>
                                </div>
                                <span class="{{ $badge($req->status) }}"><span class="{{ $dot($req->status) }}" aria-hidden="true"></span>{{ $statusLabel($req->status) }}</span>
                            </div>
                            <p class="tw-mb-0 tw-mt-2 tw-text-sm tw-text-slate-700">
                                <span class="tw-font-medium">{{ __('requisition.manage.items_summary', ['count' => $digits($req->items_count)]) }}</span>
                                <span class="tw-text-slate-500">· {{ $req->items->pluck('product.name')->filter()->take(3)->join(', ') }}{{ $req->items_count > 3 ? ' …' : '' }}</span>
                            </p>
                            <div class="tw-mt-3 tw-flex tw-flex-wrap tw-gap-2">
                                <button type="button" wire:click="openView({{ $req->id }})" class="{{ $btnSecondary }} tw-flex-1">
                                    <i class="fas fa-eye" aria-hidden="true"></i> {{ __('requisition.manage.view') }}
                                </button>
                                @if ($canEdit($req))
                                    <button type="button" wire:click="openEdit({{ $req->id }})" class="{{ $btnSecondary }} tw-flex-1">
                                        <i class="fas fa-pen" aria-hidden="true"></i> {{ __('requisition.manage.edit') }}
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $req->id }})" class="{{ $btnSecondary }} tw-px-3 tw-text-rose-600 hover:tw-text-rose-700"
                                        aria-label="{{ __('requisition.manage.delete') }} #{{ $req->id }}">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                    </button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{-- Footer: count + pagination --}}
                <div class="tw-flex tw-flex-col tw-items-center tw-gap-3 tw-border-0 tw-border-t tw-border-solid tw-border-slate-100 tw-px-4 tw-py-3 sm:tw-flex-row sm:tw-justify-between sm:tw-px-5">
                    <p class="tw-m-0 tw-text-sm tw-text-slate-500">
                        {{ __('requisition.manage.showing', ['from' => $digits($rows->firstItem()), 'to' => $digits($rows->lastItem()), 'total' => $digits($rows->total())]) }}
                    </p>
                    @include('livewire.requisitions.partials.pagination', ['paginator' => $rows, 'digits' => $digits])
                </div>
            @else
                {{-- Empty states --}}
                <div class="tw-flex tw-flex-col tw-items-center tw-px-6 tw-py-14 tw-text-center">
                    <div class="tw-mb-4 tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-brand-50 tw-text-xl tw-text-brand-600" aria-hidden="true">
                        <i class="fas {{ $hasFilters ? 'fa-search' : 'fa-clipboard-list' }}"></i>
                    </div>
                    @if ($hasFilters && $this->hasAnyRequisition)
                        <h3 class="tw-m-0 tw-text-base tw-font-semibold tw-text-slate-900">{{ __('requisition.manage.no_results_title') }}</h3>
                        <p class="tw-mb-5 tw-mt-1 tw-max-w-sm tw-text-sm tw-text-slate-500">{{ __('requisition.manage.no_results_text') }}</p>
                        <button type="button" wire:click="resetFilters" class="{{ $btnSecondary }}">
                            <i class="fas fa-times" aria-hidden="true"></i> {{ __('requisition.manage.clear_filters') }}
                        </button>
                    @else
                        <h3 class="tw-m-0 tw-text-base tw-font-semibold tw-text-slate-900">{{ __('requisition.manage.empty_title') }}</h3>
                        <p class="tw-mb-5 tw-mt-1 tw-max-w-sm tw-text-sm tw-text-slate-500">{{ __('requisition.manage.empty_text') }}</p>
                        @if ($canCreate)
                            <button type="button" wire:click="openCreate" class="{{ $btnPrimary }}">
                                <i class="fas fa-plus" aria-hidden="true"></i> {{ __('requisition.manage.new') }}
                            </button>
                        @endif
                    @endif
                </div>
            @endif
        </section>

        {{-- ============ Create / edit modal ============ --}}
        <x-ui.modal wire:model="showForm" max-width="4xl" labelledby="req-form-title">
            <form wire:submit="save('submit')" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                <div class="tw-flex tw-items-start tw-justify-between tw-gap-4 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-4 sm:tw-px-6">
                    <div class="tw-min-w-0">
                        <h2 id="req-form-title" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900">
                            {{ $editingId ? __('requisition.manage.edit_title', ['id' => $digits($editingId)]) : __('requisition.manage.create_title') }}
                        </h2>
                        <p class="tw-mb-0 tw-mt-0.5 tw-text-sm tw-text-slate-500">{{ __('requisition.manage.form_hint') }}</p>
                    </div>
                    <button type="button" x-on:click="open = false" class="{{ $iconBtn }} tw--mr-2 tw--mt-1" aria-label="{{ __('requisition.manage.close') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="tw-flex-1 tw-space-y-5 tw-overflow-y-auto tw-overscroll-contain tw-px-5 tw-py-5 sm:tw-px-6">
                    @error('form')
                        <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-xl tw-border tw-border-solid tw-border-rose-200 tw-bg-rose-50 tw-px-4 tw-py-3 tw-text-sm tw-text-rose-800" role="alert">
                            <i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    {{-- Item picker --}}
                    <fieldset class="tw-m-0 tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 tw-bg-slate-50/70 tw-p-4">
                        <legend class="tw-sr-only">{{ __('requisition.select_product') }}</legend>
                        <div class="tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-2 lg:tw-grid-cols-[1fr_1.4fr_8rem_auto] lg:tw-items-start">
                            <div>
                                <label for="req-category" class="{{ $label }}">{{ __('requisition.manage.category') }}</label>
                                <select id="req-category" wire:model.live="categoryId"
                                    class="{{ $input }} @error('categoryId') {{ $inputError }} @enderror"
                                    @error('categoryId') aria-invalid="true" aria-describedby="req-category-error" @enderror>
                                    <option value="">{{ __('requisition.manage.select_category') }}</option>
                                    @foreach ($this->categories as $category)
                                        <option value="{{ $category->id }}" wire:key="cat-{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('categoryId') <p id="req-category-error" class="{{ $errorText }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="req-product" class="{{ $label }}">{{ __('requisition.manage.product') }}</label>
                                <div class="tw-relative">
                                    <select id="req-product" wire:model="productId" @disabled(! $categoryId)
                                        wire:loading.attr="disabled" wire:target="categoryId"
                                        class="{{ $input }} @error('productId') {{ $inputError }} @enderror"
                                        @error('productId') aria-invalid="true" aria-describedby="req-product-error" @enderror>
                                        <option value="">{{ $categoryId ? __('requisition.manage.select_product') : __('requisition.manage.select_category_first') }}</option>
                                        @foreach ($this->productOptions as $product)
                                            <option value="{{ $product->id }}" wire:key="prod-{{ $product->id }}">{{ $product->name }}{{ $product->product_unit ? ' (' . $product->product_unit . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <i wire:loading wire:target="categoryId" class="fas fa-circle-notch fa-spin tw-absolute tw-right-9 tw-top-1/2 tw--translate-y-1/2 tw-text-slate-400" aria-hidden="true"></i>
                                </div>
                                @error('productId') <p id="req-product-error" class="{{ $errorText }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="req-quantity" class="{{ $label }}">{{ __('requisition.manage.quantity') }}</label>
                                <input id="req-quantity" type="number" inputmode="numeric" min="1" max="100000" step="1" wire:model="quantity"
                                    x-on:keydown.enter.prevent="$wire.addItem()"
                                    class="{{ $input }} tw-tabular-nums @error('quantity') {{ $inputError }} @enderror"
                                    @error('quantity') aria-invalid="true" aria-describedby="req-quantity-error" @enderror>
                                @error('quantity') <p id="req-quantity-error" class="{{ $errorText }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:tw-pt-[26px]">
                                <button type="button" wire:click="addItem" wire:loading.attr="disabled" wire:target="addItem"
                                    class="{{ $btnSecondary }} tw-w-full tw-border-brand-200 tw-bg-brand-50 tw-text-brand-700 hover:tw-bg-brand-100 hover:tw-text-brand-700">
                                    <i class="fas fa-plus" aria-hidden="true" wire:loading.remove wire:target="addItem"></i>
                                    <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="addItem"></i>
                                    {{ __('requisition.manage.add_item') }}
                                </button>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Selected items --}}
                    <div>
                        <div class="tw-mb-2 tw-flex tw-items-center tw-justify-between">
                            <h3 class="tw-m-0 tw-text-sm tw-font-semibold tw-text-slate-700">{{ __('requisition.manage.item_list') }}</h3>
                            @if (count($items))
                                <span class="tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-slate-600">{{ $digits(count($items)) }}</span>
                            @endif
                        </div>

                        @if (count($items))
                            @php $products = $this->itemProducts; @endphp
                            <ul class="tw-m-0 tw-list-none tw-divide-y tw-divide-slate-100 tw-overflow-hidden tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 tw-p-0">
                                @foreach ($items as $index => $item)
                                    @php
                                        $product = $products->get((int) $item['product_id']);
                                        $available = $product->available_quantity ?? 0;
                                    @endphp
                                    <li wire:key="item-{{ $item['product_id'] }}" class="tw-flex tw-flex-col tw-gap-3 tw-px-4 tw-py-3 sm:tw-flex-row sm:tw-items-center">
                                        <div class="tw-min-w-0 tw-flex-1">
                                            <p class="tw-m-0 tw-font-medium tw-text-slate-900">{{ $product->name ?? '—' }}</p>
                                            <p class="tw-m-0 tw-flex tw-flex-wrap tw-items-center tw-gap-x-2 tw-gap-y-1 tw-text-xs tw-text-slate-500">
                                                <span>{{ $product->category->name ?? '' }}</span>
                                                @if ($available > 0)
                                                    <span class="tw-inline-flex tw-items-center tw-gap-1 tw-font-medium tw-text-emerald-700"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>{{ __('requisition.manage.in_stock', ['qty' => $digits($available)]) }}</span>
                                                @else
                                                    <span class="tw-inline-flex tw-items-center tw-gap-1 tw-font-medium tw-text-rose-700"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-rose-500" aria-hidden="true"></span>{{ __('requisition.manage.out_of_stock') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="tw-flex tw-items-start tw-gap-2">
                                            <div class="tw-flex-1 sm:tw-w-28 sm:tw-flex-none">
                                                <label for="req-item-qty-{{ $index }}" class="tw-sr-only">{{ __('requisition.manage.quantity') }} — {{ $product->name ?? '' }}</label>
                                                <input id="req-item-qty-{{ $index }}" type="number" inputmode="numeric" min="1" max="100000" step="1"
                                                    wire:model.blur="items.{{ $index }}.quantity"
                                                    class="{{ $input }} tw-text-right tw-tabular-nums @error('items.' . $index . '.quantity') {{ $inputError }} @enderror"
                                                    @error('items.' . $index . '.quantity') aria-invalid="true" aria-describedby="req-item-qty-{{ $index }}-error" @enderror>
                                                @error('items.' . $index . '.quantity') <p id="req-item-qty-{{ $index }}-error" class="{{ $errorText }}">{{ $message }}</p> @enderror
                                                @error('items.' . $index . '.product_id') <p class="{{ $errorText }}">{{ $message }}</p> @enderror
                                            </div>
                                            <button type="button" wire:click="removeItem({{ $index }})" class="{{ $iconBtn }} hover:tw-bg-rose-50 hover:tw-text-rose-600 md:tw-h-11 md:tw-w-11"
                                                aria-label="{{ __('requisition.manage.remove_item_named', ['name' => $product->name ?? '']) }}" title="{{ __('requisition.manage.remove_item') }}">
                                                <i class="fas fa-times" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="tw-rounded-xl tw-border tw-border-dashed tw-border-slate-300 tw-px-4 tw-py-8 tw-text-center tw-text-sm tw-text-slate-500 @error('items') tw-border-rose-300 tw-bg-rose-50/40 @enderror">
                                <i class="fas fa-box-open tw-mb-2 tw-block tw-text-xl tw-text-slate-300" aria-hidden="true"></i>
                                {{ __('requisition.manage.no_items') }}
                            </div>
                        @endif
                        @error('items') <p class="{{ $errorText }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                    </div>

                    {{-- Remarks: stored as the first approval comment, so only offered when creating --}}
                    @unless ($editingId)
                        <div>
                            <label for="req-remarks" class="{{ $label }}">{{ __('requisition.manage.remarks') }}</label>
                            <textarea id="req-remarks" rows="2" maxlength="1000" wire:model="remarks"
                                placeholder="{{ __('requisition.manage.remarks_placeholder') }}"
                                class="{{ $input }} tw-resize-y @error('remarks') {{ $inputError }} @enderror"></textarea>
                            @error('remarks') <p class="{{ $errorText }}">{{ $message }}</p> @enderror
                        </div>
                    @endunless
                </div>

                <div class="tw-flex tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-3 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between sm:tw-px-6">
                    <p class="tw-m-0 tw-hidden tw-text-xs tw-text-slate-500 lg:tw-block">{{ __('requisition.manage.submit_hint') }}</p>
                    <div class="tw-flex tw-flex-col-reverse tw-gap-2 sm:tw-ml-auto sm:tw-flex-row">
                        <button type="button" x-on:click="open = false" class="{{ $btnSecondary }}">{{ __('requisition.manage.cancel') }}</button>
                        <button type="button" wire:click="save('save_draft')" wire:loading.attr="disabled" wire:target="save" class="{{ $btnSecondary }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save('save_draft')"></i>
                            <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="save('save_draft')"></i>
                            {{ __('requisition.manage.save_draft') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="{{ $btnPrimary }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save('submit')"></i>
                            <i class="fas fa-paper-plane" aria-hidden="true" wire:loading.remove wire:target="save('submit')"></i>
                            {{ __('requisition.manage.submit') }}
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.modal>

        {{-- ============ View modal (read-only) ============ --}}
        <x-ui.modal wire:model="showView" max-width="3xl" labelledby="req-view-title">
            @if ($viewed = $this->viewing)
                <div class="tw-flex tw-items-start tw-justify-between tw-gap-4 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-4 sm:tw-px-6">
                    <div class="tw-flex tw-min-w-0 tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1">
                        <h2 id="req-view-title" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900">{{ __('requisition.manage.view_title', ['id' => $digits($viewed->id)]) }}</h2>
                        <span class="{{ $badge($viewed->status) }}"><span class="{{ $dot($viewed->status) }}" aria-hidden="true"></span>{{ $statusLabel($viewed->status) }}</span>
                    </div>
                    <button type="button" x-on:click="open = false" class="{{ $iconBtn }} tw--mr-2 tw--mt-1" aria-label="{{ __('requisition.manage.close') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="tw-flex-1 tw-space-y-6 tw-overflow-y-auto tw-overscroll-contain tw-px-5 tw-py-5 sm:tw-px-6">
                    <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-3">
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                            <dt class="tw-text-xs tw-font-medium tw-text-slate-500">{{ __('requisition.manage.requested_on') }}</dt>
                            <dd class="tw-m-0 tw-mt-0.5 tw-font-semibold tw-text-slate-900">{{ $formatDate($viewed->requisition_date) }}</dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                            <dt class="tw-text-xs tw-font-medium tw-text-slate-500">{{ __('requisition.manage.requested_by') }}</dt>
                            <dd class="tw-m-0 tw-mt-0.5 tw-truncate tw-font-semibold tw-text-slate-900">{{ $viewed->requestedBy->name ?? '—' }}</dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                            <dt class="tw-text-xs tw-font-medium tw-text-slate-500">{{ __('requisition.manage.items') }}</dt>
                            <dd class="tw-m-0 tw-mt-0.5 tw-font-semibold tw-text-slate-900">{{ __('requisition.manage.items_summary', ['count' => $digits($viewed->items->count())]) }}</dd>
                        </div>
                    </dl>

                    <div>
                        <h3 class="tw-mb-2 tw-mt-0 tw-text-sm tw-font-semibold tw-text-slate-700">{{ __('requisition.manage.item_list') }}</h3>
                        <div class="tw-overflow-x-auto tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                            <table class="tw-m-0 tw-w-full tw-min-w-[520px] tw-border-collapse tw-text-left tw-text-sm">
                                <thead>
                                    <tr class="tw-bg-slate-50 tw-text-xs tw-font-semibold tw-text-slate-500">
                                        <th scope="col" class="tw-px-4 tw-py-2.5">{{ __('requisition.manage.product') }}</th>
                                        <th scope="col" class="tw-px-4 tw-py-2.5">{{ __('requisition.manage.brand_spec') }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-4 tw-py-2.5 tw-text-right">{{ __('requisition.manage.requested_qty') }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-4 tw-py-2.5 tw-text-right">{{ __('requisition.manage.approved_qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewed->items as $item)
                                        <tr wire:key="view-item-{{ $item->id }}" class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-100">
                                            <td class="tw-px-4 tw-py-2.5">
                                                <span class="tw-block tw-font-medium tw-text-slate-900">{{ $item->product->name ?? '—' }}</span>
                                                <span class="tw-block tw-text-xs tw-text-slate-500">{{ $item->product->category->name ?? '' }}</span>
                                            </td>
                                            <td class="tw-px-4 tw-py-2.5 tw-text-slate-600">
                                                @if ($item->lotDetail)
                                                    {{ collect([$item->lotDetail->brand, $item->lotDetail->specification])->filter()->join(' / ') ?: '—' }}
                                                @else
                                                    <span class="tw-text-slate-400">{{ __('requisition.manage.not_assigned') }}</span>
                                                @endif
                                            </td>
                                            <td class="tw-px-4 tw-py-2.5 tw-text-right tw-tabular-nums">{{ $digits($item->original_quantity ?? $item->quantity) }}</td>
                                            <td class="tw-px-4 tw-py-2.5 tw-text-right tw-font-semibold tw-tabular-nums tw-text-slate-900">
                                                {{ in_array($viewed->status, ['draft', 'pending'], true) ? '—' : $digits($item->quantity) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 class="tw-mb-3 tw-mt-0 tw-text-sm tw-font-semibold tw-text-slate-700">{{ __('requisition.manage.timeline') }}</h3>
                        <ol class="tw-relative tw-m-0 tw-list-none tw-space-y-4 tw-border-0 tw-border-l-2 tw-border-solid tw-border-slate-200 tw-py-0 tw-pl-5 tw-pr-0">
                            @foreach ($this->timeline as $step)
                                <li class="tw-relative">
                                    <span class="tw-absolute tw--left-[27px] tw-top-1 tw-flex tw-h-3 tw-w-3 tw-rounded-full tw-ring-4 tw-ring-white {{ match ($step['state']) { 'approved', 'processed' => 'tw-bg-emerald-500', 'rejected' => 'tw-bg-rose-500', 'pending' => 'tw-bg-amber-400', default => 'tw-bg-brand-500' } }}" aria-hidden="true"></span>
                                    <p class="tw-m-0 tw-text-sm tw-font-semibold tw-text-slate-900">
                                        @if ($step['state'] === 'initiated')
                                            {{ __('requisition.manage.step_initiated') }}
                                        @elseif ($step['state'] === 'pending')
                                            {{ $statusLabel('pending') }}
                                        @else
                                            {{ $statusLabel($step['state']) }}
                                        @endif
                                    </p>
                                    <p class="tw-m-0 tw-text-xs tw-text-slate-500">
                                        @if ($step['state'] === 'pending')
                                            {{ __('requisition.manage.waiting_for', ['name' => $step['name'] ?: '—']) }}
                                        @elseif ($step['name'])
                                            {{ __('requisition.manage.by', ['name' => $step['name']]) }}
                                        @endif
                                        · {{ $formatDate($step['date'], true) }}
                                    </p>
                                    @if ($step['comments'])
                                        <p class="tw-mb-0 tw-mt-1.5 tw-rounded-lg tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-sm tw-text-slate-700">{{ $step['comments'] }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        @if (count($this->timeline) === 1 && $viewed->status === 'draft')
                            <p class="tw-mb-0 tw-mt-3 tw-text-xs tw-text-slate-500">{{ __('requisition.manage.timeline_empty') }}</p>
                        @endif
                    </div>
                </div>

                <div class="tw-flex tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-3 sm:tw-flex-row sm:tw-justify-end sm:tw-px-6">
                    <button type="button" x-on:click="open = false" class="{{ $btnSecondary }}">{{ __('requisition.manage.close') }}</button>
                    @if ($viewed->is_print_enabled)
                        <a href="{{ route('requisitions.print', $viewed) }}" target="_blank" rel="noopener" class="{{ $btnSecondary }}">
                            <i class="fas fa-print" aria-hidden="true"></i> {{ __('requisition.manage.print') }}
                        </a>
                    @endif
                    @if ($canEdit($viewed))
                        <button type="button" wire:click="confirmDelete({{ $viewed->id }})" class="{{ $btnSecondary }} tw-text-rose-600 hover:tw-text-rose-700">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i> {{ __('requisition.manage.delete') }}
                        </button>
                        <button type="button" wire:click="openEdit({{ $viewed->id }})" class="{{ $btnSecondary }}">
                            <i class="fas fa-pen" aria-hidden="true"></i> {{ __('requisition.manage.edit') }}
                        </button>
                        <button type="button" wire:click="submitDraft" wire:loading.attr="disabled" wire:target="submitDraft" class="{{ $btnPrimary }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="submitDraft"></i>
                            <i class="fas fa-paper-plane" aria-hidden="true" wire:loading.remove wire:target="submitDraft"></i>
                            {{ __('requisition.manage.submit') }}
                        </button>
                    @endif
                </div>
            @endif
        </x-ui.modal>

        {{-- ============ Delete confirmation ============ --}}
        <x-ui.modal wire:model="showDelete" max-width="md" labelledby="req-delete-title" describedby="req-delete-text">
            <div class="tw-px-5 tw-pb-4 tw-pt-6 tw-text-center sm:tw-px-6">
                <div class="tw-mx-auto tw-mb-4 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-rose-50 tw-text-lg tw-text-rose-600" aria-hidden="true">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h2 id="req-delete-title" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900">{{ __('requisition.manage.delete_title') }}</h2>
                <p id="req-delete-text" class="tw-mb-0 tw-mt-2 tw-text-sm tw-text-slate-600">
                    {{ __('requisition.manage.delete_text', ['id' => $digits($deletingId), 'count' => $digits($deletingItemsCount)]) }}
                </p>
            </div>
            <div class="tw-flex tw-flex-col-reverse tw-gap-2 tw-px-5 tw-pb-5 sm:tw-flex-row sm:tw-justify-center sm:tw-px-6">
                <button type="button" x-on:click="open = false" class="{{ $btnSecondary }} sm:tw-min-w-[8rem]" autofocus>{{ __('requisition.manage.cancel') }}</button>
                <button type="button" wire:click="delete" wire:loading.attr="disabled" wire:target="delete" class="{{ $btnDanger }} sm:tw-min-w-[8rem]">
                    <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="delete"></i>
                    <span wire:loading.remove wire:target="delete">{{ __('requisition.manage.delete_confirm') }}</span>
                    <span wire:loading wire:target="delete">{{ __('requisition.manage.deleting') }}</span>
                </button>
            </div>
        </x-ui.modal>
    </div>
</div>
