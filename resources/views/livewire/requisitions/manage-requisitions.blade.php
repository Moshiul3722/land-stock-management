@php
    $label = 'tw-mb-1.5 tw-block tw-text-[13px] tw-font-semibold tw-text-slate-600';
    $input = 'tw-block tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition placeholder:tw-text-slate-400 hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15 disabled:tw-cursor-not-allowed disabled:tw-bg-slate-100 disabled:tw-text-slate-400';
    $inputError = 'tw-border-rose-400 focus:tw-border-rose-500 focus:tw-ring-rose-500/15';
    $btn = 'tw-inline-flex tw-min-h-11 tw-select-none tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-px-4 tw-text-sm tw-font-semibold tw-leading-none tw-transition hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 active:tw-translate-y-px disabled:tw-cursor-not-allowed disabled:tw-opacity-60';
    $btnPrimary = $btn . ' tw-border-brand-600 tw-bg-brand-600 tw-text-white tw-shadow-sm hover:tw-border-brand-700 hover:tw-bg-brand-700 hover:tw-text-white focus-visible:tw-ring-brand-500/30';
    $btnSecondary = $btn . ' tw-border-slate-300 tw-bg-white tw-text-slate-700 tw-shadow-sm hover:tw-bg-slate-50 hover:tw-text-slate-900 focus-visible:tw-ring-slate-400/30';
    // Footer of the details dialog: close, print preview and print each have their own colour
    $btnClose = $btn . ' tw-border-rose-600 tw-bg-rose-600 tw-text-white tw-shadow-sm hover:tw-border-rose-700 hover:tw-bg-rose-700 hover:tw-text-white focus-visible:tw-ring-rose-500/30';
    $btnPreview = $btn . ' tw-border-sky-600 tw-bg-sky-600 tw-text-white tw-shadow-sm hover:tw-border-sky-700 hover:tw-bg-sky-700 hover:tw-text-white focus-visible:tw-ring-sky-500/30';
    $btnPrint = $btn . ' tw-border-emerald-600 tw-bg-emerald-600 tw-text-white tw-shadow-sm hover:tw-border-emerald-700 hover:tw-bg-emerald-700 hover:tw-text-white focus-visible:tw-ring-emerald-500/30';
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
    <div class="tw-space-y-3 tw-pb-6 tw-pt-3 tw-font-sans tw-text-slate-800">

        {{-- ============ Page header ============ --}}
        <header class="{{ $card }} tw-relative tw-overflow-hidden tw-px-4 tw-py-4 sm:tw-px-6 sm:tw-py-5">
            <div class="tw-absolute tw-inset-x-0 tw-top-0 tw-h-1 tw-bg-gradient-to-r tw-from-brand-600 tw-via-brand-500 tw-to-accent-500" aria-hidden="true"></div>
            <div class="tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between">
                <div class="tw-min-w-0">
                    <nav aria-label="breadcrumb" class="tw-mb-1 tw-text-xs tw-text-slate-500">
                        <a href="{{ route('home') }}" class="tw-text-slate-500 hover:tw-text-brand-600">{{ __('breadcrumb.home') }}</a>
                        <span class="tw-mx-1.5 tw-text-slate-300" aria-hidden="true">/</span>
                        <span aria-current="page">{{ __('requisition.manage.list_title') }}</span>
                    </nav>
                    <h1 class="tw-m-0 tw-text-xl tw-font-bold tw-leading-snug tw-tracking-tight tw-text-slate-900 sm:tw-text-2xl">
                        {{ __('requisition.manage.list_title') }}
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
        {{-- Wide screens (xl), one row in this order: per page, status, from, to, search, clear.
             Narrower: search on top, the filters below in the same order.
             req-filters: larger text for this card (see resources/css/tailwind.css). --}}
        <section class="req-filters {{ $card }} tw-px-4 tw-py-3 sm:tw-px-5 xl:tw-flex xl:tw-items-end xl:tw-gap-3" x-data="{ more: @js($hasFilters && ($status || $dateFrom || $dateTo)) }"
            aria-label="{{ __('requisition.manage.filters') }}">
            <div class="tw-flex tw-gap-2 xl:tw-order-1 xl:tw-min-w-0 xl:tw-flex-1">
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

            <div id="req-more-filters" class="tw-mt-3 tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-2 md:tw-grid-cols-4 lg:tw-grid-cols-[auto_1fr_1fr_1fr] lg:tw-items-end"
                x-bind:class="more ? 'tw-grid xl:tw-contents' : 'tw-hidden md:tw-grid xl:tw-contents'">
                <div>
                    <label for="req-per-page" class="{{ $label }}">{{ __('requisition.manage.per_page') }}</label>
                    <select id="req-per-page" wire:model.live="perPage" class="{{ $input }} lg:tw-w-24">
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $digits($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:tw-min-w-[9rem] xl:tw-w-36">
                    <label for="req-status" class="{{ $label }}">{{ __('requisition.manage.status_filter') }}</label>
                    <select id="req-status" wire:model.live="status" class="{{ $input }}">
                        <option value="">{{ __('requisition.manage.all_statuses') }}</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}">{{ $statusLabel($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:tw-w-40">
                    <label for="req-from" class="{{ $label }}">{{ __('requisition.manage.date_from') }}</label>
                    <x-ui.date-field id="req-from" :placeholder="__('requisition.manage.date_placeholder')" wire:model.live="dateFrom" compact clearable :max="$dateTo ?: null" :pop-width="300" />
                </div>
                <div class="xl:tw-w-40">
                    <label for="req-to" class="{{ $label }}">{{ __('requisition.manage.date_to') }}</label>
                    <x-ui.date-field id="req-to" :placeholder="__('requisition.manage.date_placeholder')" wire:model.live="dateTo" compact clearable :min="$dateFrom ?: null" :pop-width="300" />
                </div>
                <div class="sm:tw-col-span-2 md:tw-col-span-4 xl:tw-order-2">
                    <button type="button" wire:click="resetFilters" @disabled(! $hasFilters)
                        class="{{ $btnSecondary }} tw-w-full xl:tw-w-auto">
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
                    <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                        <thead>
                            <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-text-sm tw-font-semibold tw-uppercase tw-tracking-wide tw-text-slate-500">
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-2">{{ __('requisition.manage.requisition_no') }}</th>
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-2">{{ __('requisition.manage.date') }}</th>
                                <th scope="col" class="tw-px-5 tw-py-2">{{ __('requisition.manage.items') }}</th>
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-2">{{ __('requisition.manage.status_label') }}</th>
                                <th scope="col" class="tw-whitespace-nowrap tw-px-5 tw-py-2 tw-text-right">{{ __('requisition.manage.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $req)
                                <tr wire:key="req-row-{{ $req->id }}" class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-transition last:tw-border-b-0 {{ $highlightId === $req->id ? 'tw-bg-amber-100' : 'hover:tw-bg-slate-50/70' }}">
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-1.5 tw-font-semibold tw-tabular-nums tw-text-slate-900">#{{ $digits($req->id) }}</td>
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-1.5 tw-text-slate-600">{{ $formatDate($req->requisition_date) }}</td>
                                    <td class="tw-w-full tw-max-w-0 tw-px-5 tw-py-1.5 xl:tw-w-auto xl:tw-max-w-md">
                                        {{-- Approved and waiting at the store: the requester is told to collect the items
                                             with the printed invoice. The note goes once the invoice is scanned (delivered). --}}
                                        <span class="tw-flex tw-flex-wrap tw-items-baseline tw-gap-x-2 tw-font-medium tw-leading-tight tw-text-slate-800">
                                            <span>{{ __('requisition.manage.items_summary', ['count' => $digits($req->items_count)]) }}</span>
                                            @if ($req->status === 'pending' && $req->is_print_enabled)
                                                <span class="tw-font-semibold tw-text-rose-600">{{ __('requisition.manage.collect_note') }}</span>
                                            @endif
                                        </span>
                                        <span class="tw-block tw-truncate tw-text-sm tw-leading-tight tw-text-slate-500" title="{{ $req->items->pluck('product.name')->filter()->join(', ') }}">
                                            {{ $req->items->pluck('product.name')->filter()->take(3)->join(', ') }}{{ $req->items_count > 3 ? ' …' : '' }}
                                        </span>
                                    </td>
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-1.5">
                                        <span class="{{ $badge($req->status) }}"><span class="{{ $dot($req->status) }}" aria-hidden="true"></span>{{ $statusLabel($req->status) }}</span>
                                    </td>
                                    <td class="tw-whitespace-nowrap tw-px-5 tw-py-0.5 tw-text-right">
                                        <div class="tw-inline-flex tw-items-center tw-gap-0.5">
                                            @if (isset($decidable[$req->id]))
                                                {{-- The signed-in user can decide on this one: the decision dialog opens
                                                     (resources/views/requisition/_decision_modal.blade.php) --}}
                                                <button type="button" class="view-action-btn {{ $iconBtn }}"
                                                    data-id="{{ $req->id }}" data-can-act="1" data-step-role="{{ $decidable[$req->id] }}"
                                                    data-url="{{ route('requisitions.get_details', $req->id) }}"
                                                    data-tip="{{ __('requisition.pending_manage.view_action') }}" aria-label="{{ __('requisition.pending_manage.view_action') }} #{{ $req->id }}">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
                                            @else
                                                <button type="button" wire:click="openView({{ $req->id }})" class="{{ $iconBtn }}"
                                                    data-tip="{{ __('requisition.manage.view') }}" aria-label="{{ __('requisition.manage.view') }} #{{ $req->id }}">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                            @if ($canEdit($req))
                                                <button type="button" wire:click="openEdit({{ $req->id }})" class="{{ $iconBtn }} hover:tw-text-brand-600"
                                                    data-tip="{{ __('requisition.manage.edit') }}" aria-label="{{ __('requisition.manage.edit') }} #{{ $req->id }}">
                                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" wire:click="confirmDelete({{ $req->id }})" class="{{ $iconBtn }} hover:tw-bg-rose-50 hover:tw-text-rose-600"
                                                    data-tip="{{ __('requisition.manage.delete') }}" aria-label="{{ __('requisition.manage.delete') }} #{{ $req->id }}">
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
                        <li wire:key="req-card-{{ $req->id }}" class="tw-px-4 tw-py-4 {{ $highlightId === $req->id ? 'tw-bg-amber-100' : '' }}">
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
                                @if ($req->status === 'pending' && $req->is_print_enabled)
                                    <span class="tw-block tw-font-semibold tw-text-rose-600">{{ __('requisition.manage.collect_note') }}</span>
                                @endif
                            </p>
                            <div class="tw-mt-3 tw-flex tw-flex-wrap tw-gap-2">
                                @if (isset($decidable[$req->id]))
                                    <button type="button" class="view-action-btn {{ $btnSecondary }} tw-flex-1"
                                        data-id="{{ $req->id }}" data-can-act="1" data-step-role="{{ $decidable[$req->id] }}"
                                        data-url="{{ route('requisitions.get_details', $req->id) }}">
                                        <i class="fas fa-eye" aria-hidden="true"></i> {{ __('requisition.pending_manage.view_action') }}
                                    </button>
                                @else
                                    <button type="button" wire:click="openView({{ $req->id }})" class="{{ $btnSecondary }} tw-flex-1">
                                        <i class="fas fa-eye" aria-hidden="true"></i> {{ __('requisition.manage.view') }}
                                    </button>
                                @endif
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
        <x-ui.modal wire:model="showForm" max-width="5xl" labelledby="req-form-title">
            {{-- req-form: larger text for everything in this dialog (see resources/css/tailwind.css) --}}
            <form wire:submit="save('submit')" class="req-form tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                <div class="tw-flex tw-flex-wrap tw-items-start tw-justify-between tw-gap-x-4 tw-gap-y-1 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-4 sm:tw-flex-nowrap sm:tw-px-6">
                    <div class="tw-min-w-0 tw-flex-1">
                        <h2 id="req-form-title" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900">
                            {{ $editingId ? __('requisition.manage.edit_title', ['id' => $digits($editingId)]) : __('requisition.manage.create_title') }}
                        </h2>
                        <p class="tw-mb-0 tw-mt-0.5 tw-text-sm tw-text-slate-500">{{ __('requisition.manage.form_hint') }}</p>
                    </div>
                    <x-ui.modal-today />
                    <button type="button" x-on:click="open = false" class="{{ $iconBtn }} tw--mr-2 tw--mt-1 tw-flex-none !tw-bg-slate-100 !tw-text-slate-600 hover:!tw-bg-slate-200 hover:!tw-text-slate-900" aria-label="{{ __('requisition.manage.close') }}">
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

                    {{-- Item picker: same fields and layout as the lot entry modal --}}
                    {{-- req-picker: shorter fields on tablets and up (see resources/css/tailwind.css) --}}
                    <fieldset class="req-picker tw-m-0 tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 tw-bg-slate-50/70 tw-p-4">
                        <legend class="tw-sr-only">{{ __('requisition.select_product') }}</legend>
                        @if ($editingIndex !== null)
                            <p class="tw-mb-3 tw-mt-0 tw-text-[13px] tw-font-semibold tw-text-brand-700" role="status">
                                <i class="fas fa-pen tw-mr-1" aria-hidden="true"></i>{{ __('requisition.manage.editing_item', ['number' => $digits($editingIndex + 1)]) }}
                            </p>
                        @endif
                        <div class="tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-2 lg:tw-grid-cols-[1fr_1.6fr_auto_auto]">
                            <div>
                                <label for="req-category" class="{{ $label }}">{{ __('requisition.manage.category') }}</label>
                                <x-ui.search-select id="req-category" wire:model.live="categoryId"
                                    :options="$this->categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name])"
                                    :placeholder="__('requisition.manage.select_category')"
                                    :invalid="$errors->has('categoryId')" />
                                @error('categoryId') <p id="req-category-error" class="{{ $errorText }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="req-product" class="{{ $label }}">{{ __('requisition.manage.product') }}</label>
                                <x-ui.search-select id="req-product" wire:model.live="productId" :disabled="! $categoryId" :visible="5"
                                    :options="$this->productOptions->map(fn ($product) => ['value' => $product->id, 'label' => $product->name . (trim((string) $product->name_bn) !== '' && $product->name_bn !== $product->name ? ' (' . $product->name_bn . ')' : '')])"
                                    :placeholder="$categoryId ? __('requisition.manage.select_product') : __('requisition.manage.select_category_first')"
                                    :invalid="$errors->has('productId')" />
                                @error('productId') <p id="req-product-error" class="{{ $errorText }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="req-quantity" class="{{ $label }}">{{ __('requisition.manage.quantity') }}</label>
                                {{-- Compact stepper in one rounded box: − | number | +. Digits show in the page language. --}}
                                <div class="tw-flex tw-min-h-11 tw-items-stretch tw-overflow-hidden tw-rounded-lg tw-border tw-border-solid tw-bg-white tw-shadow-sm tw-transition focus-within:tw-ring-4 lg:tw-inline-flex {{ $errors->has('quantity') ? 'tw-border-rose-400 focus-within:tw-border-rose-500 focus-within:tw-ring-rose-500/15' : 'tw-border-slate-300 hover:tw-border-slate-400 focus-within:tw-border-brand-500 focus-within:tw-ring-brand-500/15' }}"
                                    x-data="{
                                        value: $wire.entangle('quantity'),
                                        bangla: @js(app()->getLocale() === 'bn'),
                                        toLatin(text) { return String(text ?? '').replace(/[০-৯]/g, d => '০১২৩৪৫৬৭৮৯'.indexOf(d)).replace(/[^0-9]/g, ''); },
                                        show(text) { const latin = this.toLatin(text); return this.bangla ? latin.replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d]) : latin; },
                                        get number() { const n = parseInt(this.toLatin(this.value), 10); return Number.isNaN(n) ? null : n; },
                                        step(delta) { this.value = String(Math.min(100000, Math.max(1, (this.number ?? (delta > 0 ? 0 : 1)) + delta))); }
                                    }">
                                    {{-- Invisible sizer with a field's own text metrics: the box is always exactly as tall
                                         as the fields beside it, and never grows when an error appears under one of them. --}}
                                    <span class="tw-invisible tw-flex tw-w-0 tw-flex-none tw-overflow-hidden tw-py-2 tw-text-sm" aria-hidden="true"><span>0</span></span>
                                    <button type="button" x-on:click="step(-1)" x-bind:disabled="number !== null && number <= 1" tabindex="-1"
                                        aria-label="{{ __('commontable.decrease') }}" title="{{ __('commontable.decrease') }}"
                                        class="tw-flex tw-w-9 tw-flex-none tw-items-center tw-justify-center tw-border-0 tw-border-r tw-border-solid tw-border-slate-200 tw-bg-transparent tw-p-0 tw-text-slate-500 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 active:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-bg-brand-50 focus-visible:tw-text-brand-700 disabled:tw-cursor-not-allowed disabled:tw-bg-transparent disabled:tw-text-slate-300">
                                        <i class="fas fa-minus tw-text-xs" aria-hidden="true"></i>
                                    </button>
                                    <input type="text" id="req-quantity" inputmode="numeric" autocomplete="off" maxlength="6"
                                        role="spinbutton" aria-valuemin="1" aria-valuemax="100000" x-bind:aria-valuenow="number"
                                        x-bind:value="show(value)"
                                        x-on:input="value = toLatin($event.target.value); $event.target.value = show(value)"
                                        x-on:keydown.arrow-up.prevent="step(1)" x-on:keydown.arrow-down.prevent="step(-1)"
                                        x-on:keydown.enter.prevent="$wire.addItem()"
                                        @error('quantity') aria-invalid="true" aria-describedby="req-quantity-error" @enderror
                                        class="tw-block tw-w-12 tw-min-w-0 tw-flex-1 tw-border-0 tw-bg-transparent tw-px-1 tw-py-0 tw-text-center tw-text-base tw-font-bold tw-tabular-nums tw-text-slate-900 focus:tw-outline-none lg:tw-flex-none">
                                    <button type="button" x-on:click="step(1)" x-bind:disabled="number !== null && number >= 100000" tabindex="-1"
                                        aria-label="{{ __('commontable.increase') }}" title="{{ __('commontable.increase') }}"
                                        class="tw-flex tw-w-9 tw-flex-none tw-items-center tw-justify-center tw-border-0 tw-border-l tw-border-solid tw-border-slate-200 tw-bg-transparent tw-p-0 tw-text-slate-500 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 active:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-bg-brand-50 focus-visible:tw-text-brand-700 disabled:tw-cursor-not-allowed disabled:tw-bg-transparent disabled:tw-text-slate-300">
                                        <i class="fas fa-plus tw-text-xs" aria-hidden="true"></i>
                                    </button>
                                </div>
                                @error('quantity') <p id="req-quantity-error" class="{{ $errorText }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:tw-col-span-2 lg:tw-col-span-1">
                                <span class="{{ $label }} tw-hidden lg:tw-block" aria-hidden="true">&nbsp;</span>
                                <div class="tw-flex tw-gap-2">
                                    @if ($editingIndex !== null)
                                        <button type="button" wire:click="cancelItemEdit" class="{{ $btnSecondary }} tw-flex-1 lg:tw-flex-none">{{ __('buttons.cancel') }}</button>
                                    @endif
                                    <button type="button" wire:click="addItem" wire:loading.attr="disabled" wire:target="addItem"
                                        class="{{ $btnSecondary }} tw-flex-1 tw-border-brand-200 tw-bg-brand-50 tw-text-brand-700 hover:tw-bg-brand-100 hover:tw-text-brand-700">
                                        <i class="fas {{ $editingIndex !== null ? 'fa-check' : 'fa-plus' }}" aria-hidden="true" wire:loading.remove wire:target="addItem"></i>
                                        <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="addItem"></i>
                                        {{ $editingIndex !== null ? __('requisition.manage.update_item') : __('requisition.manage.add_item') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Selected items --}}
                    <div>
                        <div class="tw-mb-2 tw-flex tw-items-center tw-justify-between">
                            <h3 class="tw-m-0 tw-text-sm tw-font-semibold tw-text-slate-700">{{ __('requisition.manage.item_list') }}</h3>
                            @if (count($items))
                                <span class="tw-inline-flex tw-h-8 tw-min-w-[2.75rem] tw-items-center tw-justify-center tw-rounded-md tw-bg-brand-600 tw-px-3 tw-text-[15px] tw-font-bold tw-leading-none tw-text-white tw-shadow-sm"
                                    aria-label="{{ __('requisition.manage.items_summary', ['count' => $digits(count($items))]) }}">{{ $digits(count($items)) }}</span>
                            @endif
                        </div>

                        @if (count($items))
                            @php $products = $this->itemProducts; @endphp
                            <div class="tw-overflow-hidden tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                                <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-[15px] md:tw-text-base">
                                    <thead>
                                        <tr class="tw-bg-slate-50 tw-text-[13px] tw-font-semibold tw-text-slate-600 md:tw-text-sm">
                                            <th scope="col" class="tw-w-9 tw-px-1 tw-py-2 tw-text-center sm:tw-w-14 sm:tw-whitespace-nowrap sm:tw-px-3">{{ __('commontable.serial') }}</th>
                                            <th scope="col" class="tw-px-2 tw-py-2 sm:tw-px-3">{{ __('requisition.manage.item_description') }}</th>
                                            <th scope="col" class="tw-hidden tw-w-40 tw-px-3 tw-py-2 tw-text-center sm:tw-table-cell lg:tw-w-60">{{ __('requisition.manage.previous_requisition') }}</th>
                                            <th scope="col" class="tw-w-[8.5rem] tw-px-1 tw-py-2 tw-text-center sm:tw-w-44 sm:tw-px-3">{{ __('requisition.manage.required_quantity') }}</th>
                                            <th scope="col" class="tw-w-[4.75rem] tw-whitespace-nowrap tw-px-1 tw-py-2 tw-text-center sm:tw-w-24 sm:tw-px-3">{{ __('commontable.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pageItems as $index => $item)
                                            @php
                                                $product = $products->get((int) $item['product_id']);
                                                $available = $product->available_quantity ?? 0;
                                                $unit = $product && $product->product_unit && ! is_numeric($product->product_unit) ? $product->unit_label : '';
                                                $qtyInvalid = $errors->has('items.' . $index . '.quantity');
                                                // Shown only when this user has requested the product before.
                                                $previousDate = ($product->previous_requisition_date ?? null) ? $formatDate($product->previous_requisition_date) : null;
                                                $previousQuantity = $previousDate ? __('requisition.manage.previous_quantity', ['qty' => $digits($product->previous_requisition_quantity ?? 0)]) : null;
                                            @endphp
                                            <tr wire:key="item-{{ $index }}-{{ $item['product_id'] }}"
                                                class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-100 {{ $editingIndex === $index ? 'tw-bg-brand-50' : '' }}">
                                                <td class="tw-px-1 tw-py-0.5 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($index + 1) }}</td>
                                                <td class="tw-px-2 tw-py-0.5 sm:tw-px-3">
                                                    <span class="tw-block tw-break-words tw-font-medium tw-leading-tight tw-text-slate-900">{{ $product->name ?? '—' }}</span>
                                                    {{-- Category, then the stock state. The General User role sees no stock
                                                         information at all (neither "in stock" nor "out of stock"). --}}
                                                    <span class="tw-flex tw-flex-wrap tw-items-center tw-gap-x-2 tw-gap-y-1 tw-text-xs tw-leading-snug tw-text-slate-500">
                                                        <span class="tw-leading-tight">{{ $product->category->name ?? '' }}</span>
                                                        @if (! $showStockCount)
                                                            {{-- nothing about stock for the General User --}}
                                                        @elseif ($available > 0)
                                                            <span class="tw-inline-flex tw-items-center tw-gap-1 tw-font-medium tw-leading-tight tw-text-emerald-700"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>{{ $showStockCount ? __('requisition.manage.in_stock', ['qty' => $digits($available)]) : __('requisition.manage.in_stock_plain') }}</span>
                                                        @else
                                                            <span class="tw-inline-flex tw-items-center tw-gap-1 tw-font-medium tw-leading-tight tw-text-rose-700"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-rose-500" aria-hidden="true"></span>{{ __('requisition.manage.out_of_stock') }}</span>
                                                        @endif
                                                    </span>
                                                    {{-- Phones: the previous-requisition column is folded in here --}}
                                                    @if ($previousDate)
                                                        <span class="tw-block tw-text-xs tw-leading-snug tw-text-slate-500 sm:tw-hidden">{{ __('requisition.manage.previous_requisition') }}: {{ $previousDate }} ({{ $previousQuantity }})</span>
                                                    @endif
                                                    @error('items.' . $index . '.quantity') <span id="req-item-qty-{{ $index }}-error" class="tw-block tw-text-xs tw-font-medium tw-text-rose-600">{{ $message }}</span> @enderror
                                                    @error('items.' . $index . '.product_id') <span class="tw-block tw-text-xs tw-font-medium tw-text-rose-600">{{ $message }}</span> @enderror
                                                </td>
                                                <td class="tw-hidden tw-px-3 tw-py-0.5 tw-text-center tw-text-sm tw-leading-tight tw-text-slate-700 sm:tw-table-cell">
                                                    @if ($previousDate)
                                                        {{-- Date, then how many were requested that time, side by side --}}
                                                        {{-- Narrow tables may break between the two parts, never inside one --}}
                                                        <span class="tw-whitespace-nowrap">{{ $previousDate }}</span>
                                                        <span class="tw-whitespace-nowrap tw-text-xs tw-font-medium tw-text-slate-500">({{ $previousQuantity }})</span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="tw-px-1 tw-py-0.5 sm:tw-px-3">
                                                    {{-- Quantity (digits shown in the page language) with the product's unit attached on the right --}}
                                                    <div class="tw-flex tw-items-stretch tw-justify-center"
                                                        x-data="{
                                                            bangla: @js(app()->getLocale() === 'bn'),
                                                            toLatin(text) { return String(text ?? '').replace(/[০-৯]/g, d => '০১২৩৪৫৬৭৮৯'.indexOf(d)).replace(/[^0-9]/g, ''); },
                                                            show(text) { const latin = this.toLatin(text); return this.bangla ? latin.replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d]) : latin; },
                                                            get number() { const n = parseInt(this.toLatin($wire.items[{{ $index }}]?.quantity), 10); return Number.isNaN(n) ? null : n; },
                                                            step(delta) {
                                                                const next = Math.min(100000, Math.max(1, (this.number ?? (delta > 0 ? 0 : 1)) + delta));
                                                                $wire.$set('items.{{ $index }}.quantity', String(next), false);
                                                            }
                                                        }">
                                                        <label for="req-item-qty-{{ $index }}" class="tw-sr-only">{{ __('requisition.manage.required_quantity') }} — {{ $product->name ?? '' }}</label>
                                                        {{-- One rounded control: number | step arrows | unit. The focus ring is drawn around the whole control. --}}
                                                        <div class="tw-inline-flex tw-items-stretch tw-overflow-hidden tw-rounded-lg tw-border tw-border-solid tw-bg-white tw-shadow-sm tw-transition focus-within:tw-ring-2 {{ $qtyInvalid ? 'tw-border-rose-400 focus-within:tw-border-rose-500 focus-within:tw-ring-rose-500/20' : 'tw-border-slate-300 hover:tw-border-slate-400 focus-within:tw-border-brand-500 focus-within:tw-ring-brand-500/20' }}">
                                                            {{-- ০-৯ or 0-9 may be typed; the component always receives plain 0-9 --}}
                                                            <input id="req-item-qty-{{ $index }}" type="text" inputmode="numeric" autocomplete="off" maxlength="6"
                                                                role="spinbutton" aria-valuemin="1" aria-valuemax="100000" x-bind:aria-valuenow="number"
                                                                x-bind:value="show($wire.items[{{ $index }}]?.quantity)"
                                                                x-on:input="const latin = toLatin($event.target.value); $event.target.value = show(latin); $wire.$set('items.{{ $index }}.quantity', latin, false)"
                                                                x-on:keydown.arrow-up.prevent="step(1)" x-on:keydown.arrow-down.prevent="step(-1)"
                                                                class="tw-block tw-min-h-8 tw-w-10 tw-flex-none tw-border-0 tw-bg-transparent tw-px-0.5 tw-py-0 tw-text-center tw-text-base tw-font-bold tw-tabular-nums tw-text-slate-900 focus:tw-outline-none"
                                                                @if ($qtyInvalid) aria-invalid="true" aria-describedby="req-item-qty-{{ $index }}-error" @endif>
                                                            {{-- Increase / decrease, stacked like a number field's arrows --}}
                                                            <span class="tw-flex tw-w-6 tw-flex-none tw-flex-col tw-border-0 tw-border-l tw-border-solid tw-border-slate-200 sm:tw-w-5">
                                                                <button type="button" x-on:click="step(1)" x-bind:disabled="number !== null && number >= 100000" tabindex="-1"
                                                                    aria-label="{{ __('commontable.increase') }}" title="{{ __('commontable.increase') }}"
                                                                    class="tw-flex tw-flex-1 tw-items-center tw-justify-center tw-border-0 tw-bg-transparent tw-p-0 tw-text-slate-400 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 active:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-bg-brand-50 focus-visible:tw-text-brand-700 disabled:tw-cursor-not-allowed disabled:tw-bg-transparent disabled:tw-text-slate-300">
                                                                    <i class="fas fa-caret-up tw-text-xs" aria-hidden="true"></i>
                                                                </button>
                                                                <button type="button" x-on:click="step(-1)" x-bind:disabled="number !== null && number <= 1" tabindex="-1"
                                                                    aria-label="{{ __('commontable.decrease') }}" title="{{ __('commontable.decrease') }}"
                                                                    class="tw-flex tw-flex-1 tw-items-center tw-justify-center tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-transparent tw-p-0 tw-text-slate-400 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 active:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-bg-brand-50 focus-visible:tw-text-brand-700 disabled:tw-cursor-not-allowed disabled:tw-bg-transparent disabled:tw-text-slate-300">
                                                                    <i class="fas fa-caret-down tw-text-xs" aria-hidden="true"></i>
                                                                </button>
                                                            </span>
                                                            @if ($unit !== '')
                                                                <span class="tw-flex tw-flex-none tw-items-center tw-border-0 tw-border-l tw-border-solid tw-border-brand-100 tw-bg-brand-50 tw-px-2.5 tw-text-sm tw-font-semibold tw-text-brand-700 sm:tw-text-[15px]">{{ $unit }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="tw-whitespace-nowrap tw-px-0 tw-py-0 tw-text-center sm:tw-px-2">
                                                    <button type="button" wire:click="editItem({{ $index }})"
                                                        class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-md tw-border-0 tw-bg-transparent tw-text-slate-500 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 sm:tw-h-8 sm:tw-w-8"
                                                        aria-label="{{ __('buttons.edit') }}: {{ $product->name ?? '' }}" title="{{ __('buttons.edit') }}">
                                                        <i class="fas fa-pen tw-text-xs" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" wire:click="removeItem({{ $index }})"
                                                        wire:confirm="{{ __('requisition.manage.remove_item_confirm') }}"
                                                        class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-md tw-border-0 tw-bg-transparent tw-text-slate-500 tw-transition hover:tw-bg-rose-50 hover:tw-text-rose-600 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-rose-500/40 sm:tw-h-8 sm:tw-w-8"
                                                        aria-label="{{ __('requisition.manage.remove_item_named', ['name' => $product->name ?? '']) }}" title="{{ __('requisition.manage.remove_item') }}">
                                                        <i class="fas fa-trash-alt tw-text-xs" aria-hidden="true"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- More than seven items: the rest are on further pages --}}
                            @if ($itemPages > 1)
                                <nav class="tw-mt-2 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2" aria-label="{{ __('commontable.pagination') }}">
                                    <p class="tw-m-0 tw-text-xs tw-font-medium tw-text-slate-600" aria-live="polite">
                                        {{ strtr(__('commontable.dt_info'), ['_TOTAL_' => $digits(count($items)), '_START_' => $digits(($itemPage - 1) * $itemsPerPage + 1), '_END_' => $digits(min($itemPage * $itemsPerPage, count($items)))]) }}
                                    </p>
                                    <div class="tw-flex tw-flex-wrap tw-gap-1">
                                        <button type="button" wire:click="gotoItemPage({{ $itemPage - 1 }})" @disabled($itemPage === 1)
                                            aria-label="{{ __('commontable.dt_previous') }}" title="{{ __('commontable.dt_previous') }}"
                                            class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-px-2 tw-text-slate-600 tw-transition hover:tw-bg-slate-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40">
                                            <i class="fas fa-chevron-left tw-text-xs" aria-hidden="true"></i>
                                        </button>
                                        @for ($page = 1; $page <= $itemPages; $page++)
                                            <button type="button" wire:click="gotoItemPage({{ $page }})" wire:key="req-item-page-{{ $page }}"
                                                @if ($page === $itemPage) aria-current="page" @endif
                                                class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-px-2 tw-text-xs tw-font-semibold tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 {{ $page === $itemPage ? 'tw-border-brand-600 tw-bg-brand-600 tw-text-white' : 'tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50' }}">
                                                {{ $digits($page) }}
                                            </button>
                                        @endfor
                                        <button type="button" wire:click="gotoItemPage({{ $itemPage + 1 }})" @disabled($itemPage === $itemPages)
                                            aria-label="{{ __('commontable.dt_next') }}" title="{{ __('commontable.dt_next') }}"
                                            class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-px-2 tw-text-slate-600 tw-transition hover:tw-bg-slate-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40">
                                            <i class="fas fa-chevron-right tw-text-xs" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </nav>
                            @endif
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

                <div class="ui-modal-foot tw-flex tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-3 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between sm:tw-px-6">
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
                <div class="tw-flex tw-flex-wrap tw-items-start tw-justify-between tw-gap-x-4 tw-gap-y-1 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-3 sm:tw-flex-nowrap sm:tw-px-6">
                    <div class="tw-flex tw-min-w-0 tw-flex-1 tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1">
                        <h2 id="req-view-title" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900">{{ __('requisition.manage.view_title', ['id' => $digits($viewed->id)]) }}</h2>
                        <span class="{{ $badge($viewed->status) }}"><span class="{{ $dot($viewed->status) }}" aria-hidden="true"></span>{{ $statusLabel($viewed->status) }}</span>
                    </div>
                    {{-- Today's weekday and date; on phones it wraps under the title --}}
                    <p class="tw-order-last tw-m-0 tw-flex tw-w-full tw-items-center tw-gap-2 tw-text-sm tw-font-medium tw-text-slate-600 sm:tw-order-none sm:tw-ml-auto sm:tw-w-auto sm:tw-self-center sm:tw-whitespace-nowrap"
                        title="{{ __('requisition.manage.today') }}">
                        <i class="fas fa-calendar-day tw-text-brand-600" aria-hidden="true"></i>
                        <span class="tw-sr-only">{{ __('requisition.manage.today') }}:</span>
                        <time datetime="{{ now()->format('Y-m-d') }}">{{ $todayLabel }}</time>
                    </p>
                    <button type="button" x-on:click="open = false" class="{{ $iconBtn }} tw--mr-2 tw--mt-1 !tw-bg-slate-100 !tw-text-slate-600 hover:!tw-bg-slate-200 hover:!tw-text-slate-900" aria-label="{{ __('requisition.manage.close') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="tw-flex-1 tw-space-y-4 tw-overflow-y-auto tw-overscroll-contain tw-px-5 tw-py-4 sm:tw-px-6">
                    <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-3">
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_on') }}</dt>
                            <dd class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900">{{ $formatDate($viewed->requisition_date) }}</dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_by') }}</dt>
                            <dd class="tw-m-0 tw-truncate tw-font-semibold tw-leading-snug tw-text-slate-900">{{ $viewed->requestedBy->name ?? '—' }}</dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.items') }}</dt>
                            <dd class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900">{{ __('requisition.manage.items_summary', ['count' => $digits($viewed->items->count())]) }}</dd>
                        </div>
                    </dl>

                    <div>
                        <h3 class="tw-mb-2 tw-mt-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">{{ __('requisition.manage.item_list') }}</h3>
                        @php
                            // Five items per page; the page is clamped in case the list got shorter.
                            $viewTotal = $viewed->items->count();
                            $viewPages = max(1, (int) ceil($viewTotal / $viewItemsPerPage));
                            $viewPage = min(max(1, $viewItemPage), $viewPages);
                            $viewItems = $viewed->items->forPage($viewPage, $viewItemsPerPage);
                        @endphp
                        <div class="tw-overflow-x-auto tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                            <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-bg-slate-50 tw-text-base tw-font-semibold tw-text-slate-600">
                                        {{-- Same columns and names as the dialog of the requisition request list --}}
                                        <th scope="col" class="tw-w-12 tw-px-2 tw-py-2 tw-text-center sm:tw-w-20 sm:tw-whitespace-nowrap">{{ __('lot.table_sl') }}</th>
                                        <th scope="col" class="tw-px-4 tw-py-2">{{ __('requisition.manage.product') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 tw-text-right sm:tw-whitespace-nowrap">{{ __('requisition.requested_quantity') }}</th>
                                        <th scope="col" class="tw-px-4 tw-py-2 tw-text-center sm:tw-whitespace-nowrap">{{ __('requisition.approved_quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewItems as $item)
                                        @php
                                            $viewUnit = $item->product && $item->product->product_unit && ! is_numeric($item->product->product_unit) ? $item->product->unit_label : '';
                                        @endphp
                                        <tr wire:key="view-item-{{ $item->id }}" class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-100">
                                            <td class="tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600">{{ $digits(($viewPage - 1) * $viewItemsPerPage + $loop->iteration) }}</td>
                                            <td class="tw-px-4 tw-py-1.5">
                                                <span class="tw-block tw-break-words tw-font-medium tw-leading-tight tw-text-slate-900">{{ $item->product->name ?? '—' }}</span>
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500">{{ collect([$item->product->category->name ?? null, $item->product->subCategory->name ?? null])->filter()->join(' · ') }}</span>
                                            </td>
                                            <td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-right tw-tabular-nums">
                                                <span class="tw-font-semibold tw-text-slate-900">{{ $digits($item->original_quantity ?? $item->quantity) }}</span>
                                                @if ($viewUnit !== '')
                                                    <span class="tw-text-sm tw-text-slate-500">{{ $viewUnit }}</span>
                                                @endif
                                            </td>
                                            {{-- Approved quantity: shown as soon as an approver has approved the requisition
                                                 (the same figure as in the dialog of the requisition request list); a dash
                                                 while it is a draft or still waits for its first approval. --}}
                                            <td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-center tw-tabular-nums">
                                                @if ($viewed->status === 'draft' || ($viewed->status === 'pending' && ! $viewed->approvalDetails->contains('status', 'approved')))
                                                    <span class="tw-font-semibold tw-text-slate-900">—</span>
                                                @else
                                                    <span class="tw-font-semibold tw-text-slate-900">{{ $digits($item->quantity) }}</span>
                                                    @if ($viewUnit !== '')
                                                        <span class="tw-text-sm tw-text-slate-500">{{ $viewUnit }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- More than five items: the rest are on further pages --}}
                        @if ($viewPages > 1)
                            <nav class="tw-mt-2 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2" aria-label="{{ __('commontable.pagination') }}">
                                <p class="tw-m-0 tw-text-xs tw-font-medium tw-text-slate-600" aria-live="polite">
                                    {{ strtr(__('commontable.dt_info'), ['_TOTAL_' => $digits($viewTotal), '_START_' => $digits(($viewPage - 1) * $viewItemsPerPage + 1), '_END_' => $digits(min($viewPage * $viewItemsPerPage, $viewTotal))]) }}
                                </p>
                                <div class="tw-flex tw-flex-wrap tw-gap-1">
                                    <button type="button" wire:click="gotoViewItemPage({{ $viewPage - 1 }})" @disabled($viewPage === 1)
                                        aria-label="{{ __('commontable.dt_previous') }}" title="{{ __('commontable.dt_previous') }}"
                                        class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-px-2 tw-text-slate-600 tw-transition hover:tw-bg-slate-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40">
                                        <i class="fas fa-chevron-left tw-text-xs" aria-hidden="true"></i>
                                    </button>
                                    @for ($page = 1; $page <= $viewPages; $page++)
                                        <button type="button" wire:click="gotoViewItemPage({{ $page }})" wire:key="req-view-page-{{ $page }}"
                                            @if ($page === $viewPage) aria-current="page" @endif
                                            class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-px-2 tw-text-xs tw-font-semibold tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 {{ $page === $viewPage ? 'tw-border-brand-600 tw-bg-brand-600 tw-text-white' : 'tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50' }}">
                                            {{ $digits($page) }}
                                        </button>
                                    @endfor
                                    <button type="button" wire:click="gotoViewItemPage({{ $viewPage + 1 }})" @disabled($viewPage === $viewPages)
                                        aria-label="{{ __('commontable.dt_next') }}" title="{{ __('commontable.dt_next') }}"
                                        class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-px-2 tw-text-slate-600 tw-transition hover:tw-bg-slate-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40">
                                        <i class="fas fa-chevron-right tw-text-xs" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </nav>
                        @endif
                    </div>

                    <div>
                        <h3 class="tw-mb-2.5 tw-mt-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">{{ __('requisition.manage.timeline') }}</h3>
                        <ol class="tw-relative tw-m-0 tw-list-none tw-space-y-3 tw-border-0 tw-border-l-2 tw-border-solid tw-border-slate-200 tw-py-0 tw-pl-5 tw-pr-0">
                            @foreach ($this->timeline as $step)
                                <li class="tw-relative">
                                    <span class="tw-absolute tw--left-[27px] tw-top-1 tw-flex tw-h-3 tw-w-3 tw-rounded-full tw-ring-4 tw-ring-white {{ match ($step['state']) { 'approved', 'processed' => 'tw-bg-emerald-500', 'rejected' => 'tw-bg-rose-500', 'pending' => 'tw-bg-amber-400', default => 'tw-bg-brand-500' } }}" aria-hidden="true"></span>
                                    <p class="tw-m-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-900">
                                        @if ($step['state'] === 'initiated')
                                            {{ __('requisition.manage.step_initiated') }}
                                        @elseif ($step['state'] === 'pending')
                                            {{ $statusLabel('pending') }}
                                        @else
                                            {{ $statusLabel($step['state']) }}
                                        @endif
                                    </p>
                                    <p class="tw-m-0 tw-text-xs tw-leading-snug tw-text-slate-500">
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

                <div class="ui-modal-foot tw-flex tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-2.5 sm:tw-flex-row sm:tw-justify-end sm:tw-px-6">
                    @php
                        // The slip is for collecting the goods. While the requisition is still pending the
                        // buttons stay; they go once it is approved and verified by QR scan.
                        $canPrintSlip = $viewed->is_print_enabled
                            && ($viewed->status === 'pending' || ! $viewed->is_physically_verified);
                    @endphp
                    <button type="button" x-on:click="open = false" class="{{ $btnClose }}">{{ __('requisition.manage.close') }}</button>
                    @if ($canPrintSlip)
                        {{-- The slip on an A4 sheet in a new tab, without printing straight away --}}
                        <a href="{{ route('requisitions.print', ['requisition' => $viewed, 'preview' => 1]) }}" target="_blank" rel="noopener" class="{{ $btnPreview }}">
                            <i class="fas fa-eye" aria-hidden="true"></i> {{ __('requisition.manage.print_preview') }}
                        </a>
                    @endif
                    @if ($canPrintSlip)
                        <a href="{{ route('requisitions.print', $viewed) }}" target="_blank" rel="noopener" class="{{ $btnPrint }}">
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
            <div class="ui-modal-foot tw-flex tw-flex-col-reverse tw-gap-2 tw-px-5 tw-pb-5 sm:tw-flex-row sm:tw-justify-center sm:tw-px-6">
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
