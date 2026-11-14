@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
@endpush

@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn ($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
    $showDate = fn ($date) => $date
        ? $digits(\Illuminate\Support\Carbon::parse($date)->locale($isEn ? 'en' : 'bn')->translatedFormat($isEn ? 'd M Y' : 'j F Y'))
        : '—';
    // Same building blocks as the stock list
    $card = 'tw-rounded-2xl tw-border tw-border-solid tw-border-slate-200/80 tw-bg-white tw-shadow-surface';
    $label = 'tw-mb-1.5 tw-block tw-text-[13px] tw-font-semibold tw-text-slate-600';
    $input = 'tw-block tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition placeholder:tw-text-slate-400 hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15';
    $pageBtn = 'tw-inline-flex tw-h-10 tw-min-w-[2.5rem] tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-px-2 tw-text-sm tw-font-medium tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 disabled:tw-cursor-not-allowed disabled:tw-opacity-40';
    $text = $isEn ? [
        'title' => 'Product Issue History',
        'crumb' => 'Issue history',
        'requisition' => 'Requisition No.',
        'open_requisition' => 'Open this requisition',
        'department' => 'Section/Department',
        'date' => 'Date',
        'quantity' => 'Quantity',
        'total' => 'Total issued',
        'times' => 'Issues',
        'no_department' => 'No section/department',
        'search' => 'Search by requisition no., section/department or date',
        'back' => 'Back to stock list',
        'empty' => 'This product has not been issued through a requisition yet.',
    ] : [
        'title' => 'পণ্য প্রদানের বিবরণ',
        'crumb' => 'প্রদানের বিবরণ',
        'requisition' => 'রিকুইজিশন নং',
        'open_requisition' => 'এই রিকুইজিশনটি দেখুন',
        'department' => 'সেকশন/ডিপার্টমেন্ট',
        'date' => 'তারিখ',
        'quantity' => 'পরিমাণ',
        'total' => 'মোট প্রদান',
        'times' => 'মোট বার',
        'no_department' => 'সেকশন/ডিপার্টমেন্ট নেই',
        'search' => 'রিকুইজিশন নং, সেকশন/ডিপার্টমেন্ট বা তারিখ দিয়ে খুঁজুন',
        'back' => 'স্টক তালিকায় ফিরুন',
        'empty' => 'এই পণ্যটি এখনো কোনো রিকুইজিশনের মাধ্যমে প্রদান করা হয়নি।',
    ];
    $departmentName = function ($issue) use ($isEn, $text) {
        $department = $issue->user?->department;

        return $department
            ? (($isEn ? $department->name : ($department->name_bn ?: $department->name)) ?: $department->name)
            : $text['no_department'];
    };
    $totalQuantity = $issues->sum('quantity');
    // The number links to the requisition only for users who can open the requisition list.
    $canOpenRequisition = app(\App\Services\MenuPermissionService::class)->allows(Auth::user(), 'requisitions.index');
@endphp

@section('content')
    <section class="content-header list-page">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $text['title'] }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('stock.index') }}" wire:navigate>{{ __('stock.list_title') }}</a></li>
                        <li class="breadcrumb-item active">{{ $text['crumb'] }}</li>
                    </ol>
                </div>
                {{-- The product this page is about, next to the breadcrumb --}}
                <div class="col-auto list-page-context">
                    <span class="list-page-context-label">{{ __('stock.product_name') }}</span>
                    <strong class="list-page-context-value">{{ $product->name }}</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="content list-page">
        <div class="tw-root">
            <div class="tw-space-y-3 tw-pb-6 tw-font-sans tw-text-slate-800">

                {{-- ============ One row: per page, totals, search, clear, back (the product is in the page header) ============ --}}
                <section class="req-filters {{ $card }} tw-flex tw-flex-wrap tw-items-end tw-gap-x-3 tw-gap-y-3 tw-px-4 tw-py-3 sm:tw-px-5"
                    aria-label="{{ __('requisition.manage.filters') }}">
                    @if ($issues->count())
                    <div class="tw-w-28 tw-flex-none">
                        <label for="issue-per-page" class="tw-sr-only">{{ __('requisition.manage.per_page') }}</label>
                        <select id="issue-per-page" class="{{ $input }}" title="{{ __('requisition.manage.per_page') }}">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 sm:tw-flex-none">
                        <span class="tw-inline-flex tw-min-h-11 tw-items-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-bg-brand-50 tw-px-3 tw-text-sm tw-font-medium tw-text-brand-700">
                            {{ $text['total'] }}:
                            <span class="tw-font-bold tw-tabular-nums">{{ $digits($totalQuantity) }}</span>
                            @if ($unitLabel !== '') {{ $unitLabel }} @endif
                        </span>
                        <span class="tw-inline-flex tw-min-h-11 tw-items-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-bg-slate-100 tw-px-3 tw-text-sm tw-font-medium tw-text-slate-700">
                            {{ $text['times'] }}:
                            <span class="tw-font-bold tw-tabular-nums">{{ $digits($issues->count()) }}</span>
                        </span>
                    </div>
                    @if ($issues->count())
                        <div class="tw-relative tw-min-w-[12rem] tw-flex-1">
                            <label for="issue-search" class="tw-sr-only">{{ __('commontable.search') }}</label>
                            <i class="fas fa-search tw-pointer-events-none tw-absolute tw-left-3.5 tw-top-1/2 tw--translate-y-1/2 tw-text-sm tw-text-slate-400" aria-hidden="true"></i>
                            <input id="issue-search" type="search" autocomplete="off" placeholder="{{ $text['search'] }}" title="{{ $text['search'] }}" class="{{ $input }} tw-pl-10">
                        </div>
                        <button type="button" id="issue-clear"
                            class="tw-inline-flex tw-min-h-11 tw-flex-1 tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-text-sm tw-font-semibold tw-text-slate-700 tw-shadow-sm tw-transition hover:tw-bg-slate-50 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-slate-400/30 disabled:tw-cursor-not-allowed disabled:tw-opacity-50 sm:tw-flex-none"
                            disabled>
                            <i class="fas fa-times" aria-hidden="true"></i> {{ __('requisition.manage.clear_filters') }}
                        </button>
                    @endif
                    <a href="{{ route('stock.index') }}" wire:navigate
                        class="tw-inline-flex tw-min-h-11 tw-flex-1 tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-text-sm tw-font-semibold tw-text-slate-700 tw-shadow-sm tw-transition hover:tw-bg-slate-50 hover:tw-text-slate-900 hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-slate-400/30 sm:tw-ml-auto sm:tw-flex-none">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ $text['back'] }}
                    </a>
                </section>

                {{-- ============ List ============ --}}
                <section class="{{ $card }} tw-relative tw-overflow-hidden" aria-live="polite">
                    @if ($issues->count())
                        <div class="tw-overflow-x-auto">
                            <table id="issue-table" class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-text-base tw-font-semibold tw-uppercase tw-tracking-wide tw-text-slate-600">
                                        <th scope="col" class="tw-w-12 tw-whitespace-nowrap tw-px-2 tw-py-2 tw-text-center sm:tw-w-20 sm:tw-px-3">{{ __('stock.table_sl') }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 sm:tw-px-5">{{ $text['requisition'] }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 sm:tw-px-5">{{ $text['department'] }}</th>
                                        <th scope="col" class="tw-hidden tw-whitespace-nowrap tw-px-3 tw-py-2 sm:tw-table-cell sm:tw-px-5">{{ $text['date'] }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-center sm:tw-px-5">{{ $text['quantity'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($issues as $issue)
                                        @php
                                            $department = $departmentName($issue);
                                            $date = $showDate($issue->issued_date);
                                        @endphp
                                        <tr data-search="{{ mb_strtolower('#' . $issue->requisition_id . ' ' . $department . ' ' . $date . ' ' . $issue->issued_date . ' ' . $issue->user?->department?->name . ' ' . $issue->user?->department?->name_bn) }}"
                                            class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-transition hover:tw-bg-slate-50/70 last:tw-border-b-0">
                                            <td class="issue-serial tw-whitespace-nowrap tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($loop->iteration) }}</td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-1.5 tw-font-semibold tw-tabular-nums tw-text-slate-900 sm:tw-px-5">
                                                @if ($issue->requisition_id && $canOpenRequisition)
                                                    {{-- Opens this requisition's details in the requisition list --}}
                                                    <a href="{{ route('requisitions.index', ['view' => $issue->requisition_id]) }}" wire:navigate
                                                        title="{{ $text['open_requisition'] }}"
                                                        class="tw--mx-1.5 tw-inline-block tw-rounded-md tw-px-1.5 tw-py-0.5 tw-text-brand-700 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-800 hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25">#{{ $digits($issue->requisition_id) }}</a>
                                                @else
                                                    {{ $issue->requisition_id ? '#' . $digits($issue->requisition_id) : '—' }}
                                                @endif
                                            </td>
                                            <td class="tw-px-3 tw-py-1.5 sm:tw-px-5">
                                                <span class="tw-block tw-font-semibold tw-leading-tight tw-text-slate-900 tw-wrap-anywhere">{{ $department }}</span>
                                                {{-- Phones: the date is folded in here --}}
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500 sm:tw-hidden">{{ $date }}</span>
                                            </td>
                                            <td class="tw-hidden tw-whitespace-nowrap tw-px-3 tw-py-1.5 tw-text-slate-700 sm:tw-table-cell sm:tw-px-5">{{ $date }}</td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-1.5 tw-text-center tw-tabular-nums sm:tw-px-5">
                                                <span class="tw-font-semibold tw-text-slate-900">{{ $digits($issue->quantity) }}</span>
                                                @if ($unitLabel !== '')
                                                    <span class="tw-text-sm tw-text-slate-500">{{ $unitLabel }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Shown when the search matches nothing --}}
                        <p id="issue-no-match" class="tw-m-0 tw-hidden tw-px-6 tw-py-10 tw-text-center tw-text-sm tw-text-slate-500">
                            <i class="fas fa-search tw-mb-2 tw-block tw-text-xl tw-text-slate-300" aria-hidden="true"></i>
                            {{ __('requisition.manage.no_results_title') }}
                        </p>

                        {{-- Footer: count + pagination --}}
                        <div class="tw-flex tw-flex-col tw-items-center tw-gap-3 tw-border-0 tw-border-t tw-border-solid tw-border-slate-100 tw-px-4 tw-py-3 sm:tw-flex-row sm:tw-justify-between sm:tw-px-5">
                            <p id="issue-info" class="tw-m-0 tw-text-sm tw-text-slate-500"></p>
                            <nav id="issue-pager" role="navigation" aria-label="{{ __('commontable.pagination') }}" class="tw-flex tw-flex-wrap tw-items-center tw-justify-center tw-gap-1"
                                data-btn="{{ $pageBtn }}"></nav>
                        </div>
                    @else
                        <div class="tw-flex tw-flex-col tw-items-center tw-px-6 tw-py-14 tw-text-center">
                            <div class="tw-mb-4 tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-brand-50 tw-text-xl tw-text-brand-600" aria-hidden="true">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <p class="tw-m-0 tw-text-base tw-font-semibold tw-text-slate-900">{{ $text['empty'] }}</p>
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
            // ---- List: search, page size and paging in the browser (all rows are on the page) ----
            var rows = $('#issue-table tbody tr').get();
            if (rows.length === 0) { return; }
            var useBangla = @json(! $isEn);
            var infoText = @json(__('requisition.manage.showing'));
            var labels = {
                previous: @json(__('requisition.manage.previous_page')),
                next: @json(__('requisition.manage.next_page'))
            };
            var pager = $('#issue-pager');
            var buttonClass = pager.data('btn') || '';
            var idle = buttonClass + ' tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-slate-900';
            var active = buttonClass + ' tw-border-brand-600 tw-bg-brand-600 tw-text-white';
            var state = { term: '', perPage: 10, page: 1 };

            function digits(value) {
                var text = String(value);
                return useBangla ? text.replace(/[0-9]/g, function(d) { return '০১২৩৪৫৬৭৮৯'[d]; }) : text;
            }

            // ০-৯ typed in the search box match the 0-9 in the data as well.
            function normalise(text) {
                return String(text || '').toLowerCase().replace(/[০-৯]/g, function(d) { return '০১২৩৪৫৬৭৮৯'.indexOf(d); }).trim();
            }

            function render() {
                var term = normalise(state.term);
                var matched = rows.filter(function(row) {
                    return term === '' || normalise(row.getAttribute('data-search')).indexOf(term) !== -1;
                });
                var last = Math.max(1, Math.ceil(matched.length / state.perPage));
                state.page = Math.min(Math.max(1, state.page), last);
                var start = (state.page - 1) * state.perPage;
                var visible = matched.slice(start, start + state.perPage);

                rows.forEach(function(row) { row.style.display = visible.indexOf(row) === -1 ? 'none' : ''; });
                visible.forEach(function(row, index) {
                    var cell = row.querySelector('.issue-serial');
                    if (cell) { cell.textContent = digits(start + index + 1); }
                });
                $('#issue-no-match').toggleClass('tw-hidden', matched.length !== 0);
                $('#issue-table').closest('div').toggleClass('tw-hidden', matched.length === 0);
                $('#issue-clear').prop('disabled', state.term === '');

                $('#issue-info').text(matched.length === 0 ? '' : infoText
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
            $('#issue-per-page').on('change', function() {
                state.perPage = parseInt($(this).val(), 10) || 10;
                state.page = 1;
                render();
            });
            $('#issue-search').on('input', function() {
                state.term = this.value;
                state.page = 1;
                render();
            });
            $('#issue-clear').on('click', function() {
                state.term = '';
                state.page = 1;
                $('#issue-search').val('');
                render();
            });
            render();
        });
    </script>
@endpush
