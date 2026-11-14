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
    $showTime = fn ($time) => $time
        ? $digits(\Illuminate\Support\Carbon::parse($time)->locale($isEn ? 'en' : 'bn')->translatedFormat($isEn ? 'h:i A' : 'g:i A'))
        : null;
    $text = $isEn ? [
        'title' => 'Distributed Goods List',
        'crumb' => 'Distributed goods',
        'requester' => 'Requester details',
        'date' => 'Date',
        'items' => 'Items',
        'action' => 'Action',
        'view' => 'Details',
        'times' => 'Requisitions',
        'unknown' => 'Unknown',
        'no_department' => 'No section/department',
        'search' => 'Search by section, requester, requisition no., product or date',
        'empty' => 'No goods have been distributed through a requisition yet.',
    ] : [
        'title' => 'বিতরণকৃত মালামালের তালিকা',
        'crumb' => 'বিতরণকৃত মালামাল',
        'requester' => 'চাহিদাকারীর বিবরণ',
        'date' => 'তারিখ',
        'items' => 'আইটেম',
        'action' => 'কার্যক্রম',
        'view' => 'বিস্তারিত',
        'times' => 'মোট রিকুইজিশন',
        'unknown' => 'অজানা',
        'no_department' => 'সেকশন/ডিপার্টমেন্ট নেই',
        'search' => 'সেকশন, অনুরোধকারী, রিকুইজিশন নং, পণ্য বা তারিখ দিয়ে খুঁজুন',
        'empty' => 'এখনো রিকুইজিশনের মাধ্যমে কোনো মালামাল বিতরণ করা হয়নি।',
    ];
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
                        <li class="breadcrumb-item active">{{ $text['crumb'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content list-page">
        <div class="tw-root">
            <div class="tw-space-y-3 tw-pb-6 tw-font-sans tw-text-slate-800">

                {{-- ============ One row: per page, total, search, clear ============ --}}
                <section class="req-filters {{ $card }} tw-flex tw-flex-wrap tw-items-end tw-gap-x-3 tw-gap-y-3 tw-px-4 tw-py-3 sm:tw-px-5"
                    aria-label="{{ __('requisition.manage.filters') }}">
                    @if ($distributions->count())
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
                            {{ $text['times'] }}:
                            <span class="tw-font-bold tw-tabular-nums">{{ $digits($distributions->count()) }}</span>
                        </span>
                    </div>
                    @if ($distributions->count())
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
                </section>

                {{-- ============ List ============ --}}
                <section class="{{ $card }} tw-relative tw-overflow-hidden" aria-live="polite">
                    @if ($distributions->count())
                        <div class="tw-overflow-x-auto">
                            <table id="issue-table" class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-text-base tw-font-semibold tw-uppercase tw-tracking-wide tw-text-slate-600">
                                        <th scope="col" class="tw-w-12 tw-whitespace-nowrap tw-px-2 tw-py-2 tw-text-center sm:tw-w-16 sm:tw-px-3">{{ __('commontable.serial') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 sm:tw-px-5">{{ $text['requester'] }}</th>
                                        <th scope="col" class="tw-hidden tw-whitespace-nowrap tw-px-5 tw-py-2 md:tw-table-cell">{{ $text['date'] }}</th>
                                        <th scope="col" class="tw-hidden tw-px-5 tw-py-2 lg:tw-table-cell lg:tw-w-[32%]">{{ $text['items'] }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-right sm:tw-px-5">{{ $text['action'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($distributions as $row)
                                        @php
                                            $req = $row->requisition;
                                            $requester = $req->requestedBy->name ?? $text['unknown'];
                                            // Section / department of the requester, in the page language.
                                            $department = $req->requestedBy?->department;
                                            $departmentName = $department
                                                ? (($isEn ? $department->name : ($department->name_bn ?: $department->name)) ?: $department->name)
                                                : $text['no_department'];
                                            // When the goods were handed over
                                            $date = $showDate($row->issued_date);
                                            $time = $showTime($row->issued_at);
                                            $itemsText = __('requisition.manage.items_summary', ['count' => $digits($row->products->count())]);
                                            $productsShort = $row->products->take(3)->join(', ') . ($row->products->count() > 3 ? ' …' : '');
                                        @endphp
                                        <tr data-search="{{ mb_strtolower(implode(' ', [$req->id, $digits($req->id), $requester, $department?->name, $department?->name_bn, $date, $row->issued_date, $row->products->join(' ')])) }}"
                                            class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-transition hover:tw-bg-slate-50/70 last:tw-border-b-0">
                                            <td class="issue-serial tw-whitespace-nowrap tw-px-2 tw-py-1 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($loop->iteration) }}</td>
                                            <td class="tw-px-3 tw-py-1 sm:tw-px-5">
                                                <span class="tw-block tw-font-semibold tw-leading-tight tw-text-slate-900 tw-wrap-anywhere">{{ $departmentName }}</span>
                                                {{-- (requisition no.) requester --}}
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-600 tw-wrap-anywhere">
                                                    <span class="tw-font-semibold tw-tabular-nums tw-text-slate-700">(#{{ $digits($req->id) }})</span> {{ $requester }}
                                                </span>
                                                {{-- Narrower screens: the date / time and the items are folded in here --}}
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500 md:tw-hidden">
                                                    {{ $date }}@if ($time), {{ $time }}@endif
                                                </span>
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500 lg:tw-hidden">
                                                    <span class="tw-font-medium tw-text-slate-700">{{ $itemsText }}</span>@if ($productsShort !== ''): {{ $productsShort }}@endif
                                                </span>
                                            </td>
                                            <td class="tw-hidden tw-whitespace-nowrap tw-px-5 tw-py-1 md:tw-table-cell">
                                                <span class="tw-block tw-leading-tight tw-text-slate-700">{{ $date }}</span>
                                                @if ($time)
                                                    <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500">
                                                        <i class="fas fa-clock tw-mr-1 tw-text-xs tw-text-slate-400" aria-hidden="true"></i>{{ $time }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="tw-hidden tw-px-5 tw-py-1 lg:tw-table-cell lg:tw-w-[32%] lg:tw-max-w-0">
                                                <span class="tw-block tw-font-medium tw-leading-tight tw-text-slate-800">{{ $itemsText }}</span>
                                                <span class="tw-block tw-truncate tw-text-sm tw-leading-tight tw-text-slate-500" title="{{ $row->products->join(', ') }}">{{ $productsShort }}</span>
                                            </td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-1 tw-text-right sm:tw-px-5">
                                                {{-- Opens this requisition's details in the dialog below, on this page --}}
                                                <button type="button" class="dist-view-btn tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-border-brand-200 tw-bg-brand-50 tw-text-sm tw-text-brand-700 tw-transition hover:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25"
                                                    data-id="{{ $req->id }}" data-url="{{ route('requisitions.get_details', $req->id) }}"
                                                    data-tip="{{ $text['view'] }}" aria-label="{{ $text['view'] }} #{{ $req->id }}">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </button>
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

    {{-- ============ Requisition details (read-only), opened by a row's view button ============
         Same content and look as the view dialog of the requisition list; Bootstrap opens and
         closes it, the page script below fills it. --}}
    @php
        $todayLabel = $digits(now()->locale($isEn ? 'en' : 'bn')->translatedFormat($isEn ? 'l, d M Y' : 'l, j F Y'));
        $badgeBase = 'tw-inline-flex tw-items-center tw-gap-1.5 tw-whitespace-nowrap tw-rounded-full tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-ring-1 tw-ring-inset';
        $badgeTones = [
            'pending' => ['tw-bg-amber-50 tw-text-amber-800 tw-ring-amber-300', 'tw-bg-amber-500'],
            'approved' => ['tw-bg-emerald-50 tw-text-emerald-800 tw-ring-emerald-300', 'tw-bg-emerald-500'],
            'completed' => ['tw-bg-emerald-50 tw-text-emerald-800 tw-ring-emerald-300', 'tw-bg-emerald-500'],
            'processed' => ['tw-bg-sky-50 tw-text-sky-800 tw-ring-sky-300', 'tw-bg-sky-500'],
            'delivered' => ['tw-bg-sky-50 tw-text-sky-800 tw-ring-sky-300', 'tw-bg-sky-500'],
            'rejected' => ['tw-bg-rose-50 tw-text-rose-800 tw-ring-rose-300', 'tw-bg-rose-500'],
            'default' => ['tw-bg-slate-100 tw-text-slate-700 tw-ring-slate-300', 'tw-bg-slate-400'],
        ];
        // Texts and classes the dialog's script needs (built here: Blade's @json cannot take nested arrays inline)
        $distTexts = [
            'title' => __('requisition.manage.view_title', ['id' => ':id']),
            'items' => __('requisition.manage.items_summary', ['count' => ':count']),
            'initiated' => __('requisition.manage.step_initiated'),
            'by' => __('requisition.manage.by', ['name' => ':name']),
            'waiting' => __('requisition.manage.waiting_for', ['name' => ':name']),
            'info' => __('commontable.dt_info'),
            'loading' => $isEn ? 'Loading…' : 'লোড হচ্ছে…',
            'failed' => $isEn ? 'The details could not be loaded. Please try again.' : 'বিস্তারিত লোড করা যায়নি। আবার চেষ্টা করুন।',
            'statuses' => trans('requisition.manage.status'),
            'tones' => $badgeTones,
            'badgeBase' => $badgeBase,
            'months' => $isEn
                ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                : ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'],
        ];
        $itemPageBtn = 'tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-px-2 tw-text-xs tw-font-semibold tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40';
    @endphp
    <div class="modal fade tw-root" id="distViewModal" tabindex="-1" role="dialog" aria-labelledby="distViewTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content tw-font-sans tw-text-slate-800">
                <div class="tw-flex tw-flex-none tw-flex-wrap tw-items-start tw-justify-between tw-gap-x-4 tw-gap-y-1 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-3 sm:tw-flex-nowrap sm:tw-px-6">
                    <div class="tw-flex tw-min-w-0 tw-flex-1 tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1">
                        <h2 id="distViewTitle" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900"></h2>
                        <span id="dist-status" class="{{ $badgeBase }}"><span id="dist-status-dot" class="tw-h-1.5 tw-w-1.5 tw-rounded-full" aria-hidden="true"></span><span id="dist-status-text"></span></span>
                    </div>
                    {{-- Today's weekday and date; on phones it wraps under the title --}}
                    <p class="tw-order-last tw-m-0 tw-flex tw-w-full tw-items-center tw-gap-2 tw-text-sm tw-font-medium tw-text-slate-600 sm:tw-order-none sm:tw-ml-auto sm:tw-w-auto sm:tw-self-center sm:tw-whitespace-nowrap"
                        title="{{ __('requisition.manage.today') }}">
                        <i class="fas fa-calendar-day tw-text-brand-600" aria-hidden="true"></i>
                        <span class="tw-sr-only">{{ __('requisition.manage.today') }}:</span>
                        <time datetime="{{ now()->format('Y-m-d') }}">{{ $todayLabel }}</time>
                    </p>
                    <button type="button" data-dismiss="modal" aria-label="{{ __('requisition.manage.close') }}"
                        class="tw--mr-2 tw--mt-1 tw-inline-flex tw-h-11 tw-w-11 tw-flex-none tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-border-transparent tw-bg-slate-100 tw-text-slate-600 tw-transition hover:tw-bg-slate-200 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 md:tw-h-9 md:tw-w-9">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-body tw-space-y-4 tw-px-5 tw-py-4 sm:tw-px-6">
                    <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-3">
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_on') }}</dt>
                            <dd id="dist-date" class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900"></dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_by') }}</dt>
                            <dd id="dist-requester" class="tw-m-0 tw-truncate tw-font-semibold tw-leading-snug tw-text-slate-900"></dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.items') }}</dt>
                            <dd id="dist-count" class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900"></dd>
                        </div>
                    </dl>

                    <div>
                        <h3 class="tw-mb-2 tw-mt-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">{{ __('requisition.manage.item_list') }}</h3>
                        <div class="tw-overflow-x-auto tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                            <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-bg-slate-50 tw-text-base tw-font-semibold tw-text-slate-600">
                                        <th scope="col" class="tw-w-12 tw-px-2 tw-py-2 tw-text-center sm:tw-w-20 sm:tw-whitespace-nowrap">{{ __('lot.table_sl') }}</th>
                                        <th scope="col" class="tw-px-4 tw-py-2">{{ __('requisition.manage.product') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 tw-text-right sm:tw-whitespace-nowrap">{{ __('requisition.requested_quantity') }}</th>
                                        <th scope="col" class="tw-px-4 tw-py-2 tw-text-center sm:tw-whitespace-nowrap">{{ __('requisition.approved_quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="dist-items"></tbody>
                            </table>
                        </div>
                        {{-- More than five items: the rest are on further pages --}}
                        <nav id="dist-items-nav" class="tw-mt-2 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2" aria-label="{{ __('commontable.pagination') }}" style="display: none;">
                            <p id="dist-items-info" class="tw-m-0 tw-text-xs tw-font-medium tw-text-slate-600" aria-live="polite"></p>
                            <div id="dist-items-pager" class="tw-flex tw-flex-wrap tw-gap-1" data-btn="{{ $itemPageBtn }}"></div>
                        </nav>
                    </div>

                    <div>
                        <h3 class="tw-mb-2.5 tw-mt-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">{{ __('requisition.manage.timeline') }}</h3>
                        <ol id="dist-timeline" class="tw-relative tw-m-0 tw-list-none tw-space-y-3 tw-border-0 tw-border-l-2 tw-border-solid tw-border-slate-200 tw-py-0 tw-pl-5 tw-pr-0"></ol>
                    </div>
                </div>

                <div class="ui-modal-foot tw-flex tw-flex-none tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-2.5 sm:tw-flex-row sm:tw-justify-end sm:tw-px-6">
                    <button type="button" data-dismiss="modal"
                        class="tw-inline-flex tw-min-h-11 tw-select-none tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-4 tw-text-sm tw-font-semibold tw-leading-none tw-text-slate-700 tw-shadow-sm tw-transition hover:tw-bg-slate-50 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-slate-400/30">{{ __('requisition.manage.close') }}</button>
                </div>
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

    {{-- The details dialog: filled from the requisition's details when a row's view button is pressed --}}
    <script>
        $(document).ready(function() {
            var useBangla = @json(! $isEn);
            var T = @json($distTexts);
            var perPage = 5, items = [], itemPage = 1, showApproved = true, request = null;

            function digits(value) {
                var text = String(value);
                return useBangla ? text.replace(/[0-9]/g, function(d) { return '০১২৩৪৫৬৭৮৯'[d]; }) : text;
            }
            function esc(value) { return $('<div>').text(value == null ? '' : String(value)).html(); }
            function statusLabel(status) { return T.statuses[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : ''); }

            // "১৪ নভেম্বর ২০২৬" and, with the time, "১৪ নভেম্বর ২০২৬, ১১:৪৪ দুপুর" (Dhaka time, as the list shows it)
            function showDate(iso, withTime) {
                var parts = String(iso || '').match(/^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}):(\d{2}))?/);
                if (!parts) { return '—'; }
                var text = useBangla
                    ? digits(Number(parts[3])) + ' ' + T.months[Number(parts[2]) - 1] + ' ' + digits(parts[1])
                    : parts[3] + ' ' + T.months[Number(parts[2]) - 1] + ' ' + parts[1];
                if (withTime && parts[4] !== undefined) {
                    var hour = Number(parts[4]), hour12 = hour % 12 === 0 ? 12 : hour % 12;
                    var period = useBangla
                        ? (hour < 4 ? 'রাত' : hour < 10 ? 'সকাল' : hour < 17 ? 'দুপুর' : hour < 20 ? 'বিকাল' : 'রাত')
                        : (hour < 12 ? 'AM' : 'PM');
                    text += ', ' + digits(hour12) + ':' + digits(parts[5]) + ' ' + period;
                }
                return text;
            }

            function setStatus(status) {
                var tone = T.tones[status] || T.tones['default'];
                $('#dist-status').attr('class', T.badgeBase + ' ' + tone[0]);
                $('#dist-status-dot').attr('class', 'tw-h-1.5 tw-w-1.5 tw-rounded-full ' + tone[1]);
                $('#dist-status-text').text(statusLabel(status));
            }

            function renderItems() {
                var pages = Math.max(1, Math.ceil(items.length / perPage));
                itemPage = Math.min(Math.max(1, itemPage), pages);
                var start = (itemPage - 1) * perPage;
                var html = items.slice(start, start + perPage).map(function(item, index) {
                    var product = item.product || {};
                    var unit = product.product_unit && isNaN(Number(product.product_unit)) ? (product.unit_label || '') : '';
                    var unitHtml = unit !== '' ? ' <span class="tw-text-sm tw-text-slate-500">' + esc(unit) + '</span>' : '';
                    var groups = [product.category && product.category.name, product.sub_category && product.sub_category.name].filter(Boolean).join(' · ');
                    var requested = item.original_quantity != null ? item.original_quantity : item.quantity;
                    return '<tr class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-100">' +
                        '<td class="tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600">' + digits(start + index + 1) + '</td>' +
                        '<td class="tw-px-4 tw-py-1.5"><span class="tw-block tw-break-words tw-font-medium tw-leading-tight tw-text-slate-900">' + esc(product.name || '—') + '</span>' +
                        '<span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500">' + esc(groups) + '</span></td>' +
                        '<td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-right tw-tabular-nums"><span class="tw-font-semibold tw-text-slate-900">' + digits(requested) + '</span>' + unitHtml + '</td>' +
                        '<td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-center tw-tabular-nums">' +
                        (showApproved ? '<span class="tw-font-semibold tw-text-slate-900">' + digits(item.quantity) + '</span>' + unitHtml : '<span class="tw-font-semibold tw-text-slate-900">—</span>') +
                        '</td></tr>';
                }).join('');
                $('#dist-items').html(html);

                $('#dist-items-nav').css('display', pages > 1 ? 'flex' : 'none');
                if (pages > 1) {
                    $('#dist-items-info').text(T.info.replace('_TOTAL_', digits(items.length)).replace('_START_', digits(start + 1)).replace('_END_', digits(Math.min(start + perPage, items.length))));
                    var pager = $('#dist-items-pager').empty(), base = pager.data('btn') || '';
                    for (var page = 1; page <= pages; page++) {
                        var button = $('<button type="button">').text(digits(page)).attr('data-page', page)
                            .addClass(base + ' ' + (page === itemPage ? 'tw-border-brand-600 tw-bg-brand-600 tw-text-white' : 'tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50'));
                        if (page === itemPage) { button.attr('aria-current', 'page'); }
                        pager.append(button);
                    }
                }
            }

            function renderTimeline(timeline) {
                var colours = { approved: 'tw-bg-emerald-500', processed: 'tw-bg-emerald-500', rejected: 'tw-bg-rose-500', pending: 'tw-bg-amber-400' };
                $('#dist-timeline').html((timeline || []).map(function(step) {
                    var state = step.status_raw || '';
                    var title = state === 'initiated' ? T.initiated : statusLabel(state);
                    var who = state === 'pending' ? T.waiting.replace(':name', step.pending_users || '—') : (step.user ? T.by.replace(':name', step.user) : '');
                    return '<li class="tw-relative">' +
                        '<span class="tw-absolute tw--left-[27px] tw-top-1 tw-flex tw-h-3 tw-w-3 tw-rounded-full tw-ring-4 tw-ring-white ' + (colours[state] || 'tw-bg-brand-600') + '" aria-hidden="true"></span>' +
                        '<p class="tw-m-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-900">' + esc(title) + '</p>' +
                        '<p class="tw-m-0 tw-text-xs tw-leading-snug tw-text-slate-500">' + esc(who) + ' · ' + esc(showDate(step.date_iso, true)) + '</p>' +
                        (step.comments ? '<p class="tw-mb-0 tw-mt-1.5 tw-rounded-lg tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-sm tw-text-slate-700">' + esc(step.comments) + '</p>' : '') +
                        '</li>';
                }).join(''));
            }

            $(document).on('click', '.dist-view-btn', function() {
                var id = $(this).data('id');
                $('#distViewTitle').text(T.title.replace(':id', digits(id)));
                $('#dist-status').attr('class', 'tw-hidden');
                $('#dist-date, #dist-requester, #dist-count').text('…');
                $('#dist-items').html('<tr><td colspan="4" class="tw-px-4 tw-py-6 tw-text-center tw-text-sm tw-text-slate-500">' + esc(T.loading) + '</td></tr>');
                $('#dist-items-nav').hide();
                $('#dist-timeline').html('<li class="tw-text-sm tw-text-slate-500">' + esc(T.loading) + '</li>');
                $('#distViewModal').modal('show');

                if (request) { request.abort(); }
                request = $.ajax({ url: $(this).data('url'), type: 'GET' })
                    .done(function(res) {
                        var requisition = res.requisition || {};
                        items = requisition.items || [];
                        itemPage = 1;
                        // A dash until an approver has approved it (as in the requisition list's dialog)
                        showApproved = !(requisition.status === 'draft' || (requisition.status === 'pending' &&
                            !(res.timeline || []).some(function(step) { return step.status_raw === 'approved'; })));
                        setStatus(requisition.status);
                        $('#dist-date').text(showDate(res.date_iso, false));
                        $('#dist-requester').text(res.requestor || '—');
                        $('#dist-count').text(T.items.replace(':count', digits(items.length)));
                        renderItems();
                        renderTimeline(res.timeline);
                    })
                    .fail(function(xhr, status) {
                        if (status === 'abort') { return; }
                        $('#dist-items').html('<tr><td colspan="4" class="tw-px-4 tw-py-6 tw-text-center tw-text-sm tw-text-rose-600">' + esc(T.failed) + '</td></tr>');
                        $('#dist-timeline').empty();
                    });
            });
            $('#dist-items-pager').on('click', 'button', function() {
                itemPage = parseInt($(this).attr('data-page'), 10) || 1;
                renderItems();
            });
        });
    </script>
@endpush
