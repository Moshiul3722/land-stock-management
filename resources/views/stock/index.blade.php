@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
@endpush

@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn ($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
    // Same building blocks as the requisition list
    $card = 'tw-rounded-2xl tw-border tw-border-solid tw-border-slate-200/80 tw-bg-white tw-shadow-surface';
    $label = 'tw-mb-1.5 tw-block tw-text-[13px] tw-font-semibold tw-text-slate-600';
    $input = 'tw-block tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition placeholder:tw-text-slate-400 hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15';
    $pageBtn = 'tw-inline-flex tw-h-10 tw-min-w-[2.5rem] tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-px-2 tw-text-sm tw-font-medium tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 disabled:tw-cursor-not-allowed disabled:tw-opacity-40';
    $categoryNames = $stockSummary->pluck('category_name')->filter()->unique()->sort()->values();
    $searchPlaceholder = $isEn ? 'Search by product or category' : 'পণ্য বা ক্যাটাগরির নাম দিয়ে খুঁজুন';
    $emptyText = $isEn ? 'There is no product in stock yet.' : 'স্টকে এখনো কোনো পণ্য নেই।';
@endphp

@section('content')
    <section class="content-header list-page">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('stock.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.list') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content list-page">
        <div class="tw-root">
            <div class="tw-space-y-3 tw-pb-6 tw-font-sans tw-text-slate-800">

                {{-- ============ Filters: per page, category, search, clear ============ --}}
                <section class="req-filters {{ $card }} tw-grid tw-grid-cols-2 tw-items-end tw-gap-3 tw-px-4 tw-py-3 sm:tw-flex sm:tw-flex-wrap sm:tw-px-5 xl:tw-flex-nowrap"
                    aria-label="{{ __('requisition.manage.filters') }}">
                    <div class="sm:tw-w-28 sm:tw-flex-none">
                        <label for="stock-per-page" class="{{ $label }} !tw-mb-1">{{ __('requisition.manage.per_page') }}</label>
                        <select id="stock-per-page" class="{{ $input }}">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:tw-w-56 sm:tw-flex-none">
                        <label for="stock-category" class="{{ $label }} !tw-mb-1">{{ __('stock.table_category') }}</label>
                        <select id="stock-category" class="{{ $input }}">
                            <option value="">{{ __('product.manage.all_categories') }}</option>
                            @foreach ($categoryNames as $categoryName)
                                <option value="{{ $categoryName }}">{{ $categoryName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tw-relative tw-col-span-2 tw-min-w-0 sm:tw-order-last sm:tw-w-full xl:tw-order-none xl:tw-w-auto xl:tw-flex-1">
                        <label for="stock-search" class="tw-sr-only">{{ __('commontable.search') }}</label>
                        <i class="fas fa-search tw-pointer-events-none tw-absolute tw-left-3.5 tw-top-1/2 tw--translate-y-1/2 tw-text-sm tw-text-slate-400" aria-hidden="true"></i>
                        <input id="stock-search" type="search" autocomplete="off" placeholder="{{ $searchPlaceholder }}" class="{{ $input }} tw-pl-10">
                    </div>
                    <button type="button" id="stock-clear"
                        class="tw-col-span-2 tw-inline-flex tw-min-h-11 tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-4 tw-text-sm tw-font-semibold tw-text-slate-700 tw-shadow-sm tw-transition hover:tw-bg-slate-50 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-slate-400/30 disabled:tw-cursor-not-allowed disabled:tw-opacity-50 sm:tw-ml-auto sm:tw-flex-none xl:tw-ml-0"
                        disabled>
                        <i class="fas fa-times" aria-hidden="true"></i> {{ __('requisition.manage.clear_filters') }}
                    </button>
                </section>

                {{-- ============ List ============ --}}
                <section class="{{ $card }} tw-relative tw-overflow-hidden" aria-labelledby="stock-list-title" aria-live="polite">
                    <h2 id="stock-list-title" class="tw-sr-only">{{ __('stock.list_title') }}</h2>

                    @if ($stockSummary->count())
                        <div class="tw-overflow-x-auto">
                            <table id="stock-table" class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-text-base tw-font-semibold tw-uppercase tw-tracking-wide tw-text-slate-600">
                                        <th scope="col" class="tw-w-12 tw-whitespace-nowrap tw-px-2 tw-py-2 tw-text-center sm:tw-w-20 sm:tw-px-3">{{ __('stock.table_sl') }}</th>
                                        <th scope="col" class="tw-hidden tw-px-5 tw-py-2 md:tw-table-cell">{{ __('stock.table_category') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 sm:tw-px-5">{{ __('stock.table_product') }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-center sm:tw-px-5">{{ __('stock.table_quantity') }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-right sm:tw-px-5">{{ __('stock.table_action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockSummary as $item)
                                        <tr data-category="{{ $item->category_name }}"
                                            data-search="{{ mb_strtolower($item->product_name . ' ' . ($banglaNames[$item->product_id] ?? '') . ' ' . $item->category_name) }}"
                                            class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-transition hover:tw-bg-slate-50/70 last:tw-border-b-0">
                                            {{-- Serial number: set by the list script, so it follows the filters and the page --}}
                                            <td class="stock-serial tw-whitespace-nowrap tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($loop->iteration) }}</td>
                                            <td class="tw-hidden tw-px-5 tw-py-1.5 tw-text-slate-700 md:tw-table-cell">{{ $item->category_name }}</td>
                                            <td class="tw-px-3 tw-py-1.5 sm:tw-px-5">
                                                {{-- Opens the page that lists which departments received this product, when and how many --}}
                                                <a href="{{ route('stock.issues', $item->product_id) }}" wire:navigate
                                                    title="{{ $isEn ? 'See who received this product' : 'কোন ডিপার্টমেন্ট কবে কতটি নিয়েছে দেখুন' }}"
                                                    class="tw--mx-1.5 tw-inline-block tw-rounded-md tw-px-1.5 tw-py-0.5 tw-font-semibold tw-leading-tight tw-text-slate-900 tw-transition tw-wrap-anywhere hover:tw-bg-brand-50 hover:tw-text-brand-700 hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25">{{ $item->product_name . (($banglaNames[$item->product_id] ?? '') !== '' ? ' (' . $banglaNames[$item->product_id] . ')' : '') }}</a>
                                                {{-- Narrower screens: the category is folded in here --}}
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500 md:tw-hidden">{{ $item->category_name }}</span>
                                            </td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-1.5 tw-text-center tw-tabular-nums sm:tw-px-5">
                                                {{-- Quantity with the product's unit --}}
                                                <span class="tw-font-semibold tw-text-slate-900">{{ $digits($item->total_quantity) }}</span>
                                                @if (($unitLabels[$item->product_id] ?? '') !== '')
                                                    <span class="tw-text-sm tw-text-slate-500">{{ $unitLabels[$item->product_id] }}</span>
                                                @endif
                                            </td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-1.5 tw-text-right sm:tw-px-5">
                                                <a href="{{ route('stock.show', $item->product_id) }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-border-transparent tw-text-slate-500 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25"
                                                    data-tip="{{ __('stock.details_title') }}" aria-label="{{ __('stock.details_title') }}: {{ $item->product_name }}">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Shown when the filters match nothing --}}
                        <p id="stock-no-match" class="tw-m-0 tw-hidden tw-px-6 tw-py-10 tw-text-center tw-text-sm tw-text-slate-500">
                            <i class="fas fa-search tw-mb-2 tw-block tw-text-xl tw-text-slate-300" aria-hidden="true"></i>
                            {{ __('requisition.manage.no_results_title') }}
                        </p>

                        {{-- Footer: count + pagination --}}
                        <div class="tw-flex tw-flex-col tw-items-center tw-gap-3 tw-border-0 tw-border-t tw-border-solid tw-border-slate-100 tw-px-4 tw-py-3 sm:tw-flex-row sm:tw-justify-between sm:tw-px-5">
                            <p id="stock-info" class="tw-m-0 tw-text-sm tw-text-slate-500"></p>
                            <nav id="stock-pager" role="navigation" aria-label="{{ __('commontable.pagination') }}" class="tw-flex tw-flex-wrap tw-items-center tw-justify-center tw-gap-1"
                                data-btn="{{ $pageBtn }}"></nav>
                        </div>
                    @else
                        <div class="tw-flex tw-flex-col tw-items-center tw-px-6 tw-py-14 tw-text-center">
                            <div class="tw-mb-4 tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-brand-50 tw-text-xl tw-text-brand-600" aria-hidden="true">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <p class="tw-m-0 tw-text-base tw-font-semibold tw-text-slate-900">{{ $emptyText }}</p>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // ---- List: category filter, search, page size and paging in the browser (all rows are on the page) ----
            var rows = $('#stock-table tbody tr').get();
            var useBangla = @json(! $isEn);
            var infoText = @json(__('requisition.manage.showing'));
            var labels = {
                previous: @json(__('requisition.manage.previous_page')),
                next: @json(__('requisition.manage.next_page'))
            };
            var pager = $('#stock-pager');
            var buttonClass = pager.data('btn') || '';
            var idle = buttonClass + ' tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-slate-900';
            var active = buttonClass + ' tw-border-brand-600 tw-bg-brand-600 tw-text-white';
            var state = { term: '', category: '', perPage: 10, page: 1 };

            function digits(value) {
                var text = String(value);
                return useBangla ? text.replace(/[0-9]/g, function(d) { return '০১২৩৪৫৬৭৮৯'[d]; }) : text;
            }

            function normalise(text) {
                return String(text || '').toLowerCase().trim();
            }

            function render() {
                var term = normalise(state.term);
                var matched = rows.filter(function(row) {
                    return (state.category === '' || row.getAttribute('data-category') === state.category) &&
                        (term === '' || normalise(row.getAttribute('data-search')).indexOf(term) !== -1);
                });
                var last = Math.max(1, Math.ceil(matched.length / state.perPage));
                state.page = Math.min(Math.max(1, state.page), last);
                var start = (state.page - 1) * state.perPage;
                var visible = matched.slice(start, start + state.perPage);

                rows.forEach(function(row) { row.style.display = visible.indexOf(row) === -1 ? 'none' : ''; });
                // Serial numbers follow the filters and the page.
                visible.forEach(function(row, index) {
                    var cell = row.querySelector('.stock-serial');
                    if (cell) { cell.textContent = digits(start + index + 1); }
                });
                $('#stock-no-match').toggleClass('tw-hidden', matched.length !== 0);
                $('#stock-table').closest('div').toggleClass('tw-hidden', matched.length === 0);
                $('#stock-clear').prop('disabled', state.term === '' && state.category === '');

                $('#stock-info').text(matched.length === 0 ? '' : infoText
                    .replace(':total', digits(matched.length))
                    .replace(':from', digits(start + 1))
                    .replace(':to', digits(start + visible.length)));

                pager.empty();
                if (last > 1) {
                    var add = function(html, page, options) {
                        var button = $('<button type="button">').html(html).addClass(options.active ? active : idle)
                            .prop('disabled', !!options.disabled).attr('data-page', page);
                        if (options.label) { button.attr('aria-label', options.label); }
                        if (options.active) { button.attr('aria-current', 'page'); }
                        pager.append(button);
                    };
                    add('<i class="fas fa-chevron-left tw-text-xs" aria-hidden="true"></i>', state.page - 1, { disabled: state.page === 1, label: labels.previous });
                    for (var page = 1; page <= last; page++) {
                        if (page === 1 || page === last || Math.abs(page - state.page) <= 1) {
                            add(digits(page), page, { active: page === state.page });
                        } else if (Math.abs(page - state.page) === 2) {
                            pager.append($('<span class="tw-px-1 tw-text-slate-400" aria-hidden="true">').text('…'));
                        }
                    }
                    add('<i class="fas fa-chevron-right tw-text-xs" aria-hidden="true"></i>', state.page + 1, { disabled: state.page === last, label: labels.next });
                }
            }

            pager.on('click', 'button', function() {
                state.page = parseInt($(this).attr('data-page'), 10) || 1;
                render();
            });
            $('#stock-per-page').on('change', function() {
                state.perPage = parseInt($(this).val(), 10) || 10;
                state.page = 1;
                render();
            });
            $('#stock-category').on('change', function() {
                state.category = $(this).val();
                state.page = 1;
                render();
            });
            $('#stock-search').on('input', function() {
                state.term = this.value;
                state.page = 1;
                render();
            });
            $('#stock-clear').on('click', function() {
                state.term = '';
                state.category = '';
                state.page = 1;
                $('#stock-search').val('');
                $('#stock-category').val('');
                render();
            });
            render();
        });
    </script>
@endpush
