@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
    <style>
        /* Timeline CSS */
        .timeline-container {
            position: relative;
            padding: 10px 0;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            top: 15px;
            bottom: 0;
            left: 15px;
            width: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .timeline-entry {
            position: relative;
            margin-bottom: 25px;
            padding-left: 50px;
            z-index: 1;
        }

        .timeline-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            font-weight: bold;
            font-size: 14px;
            color: #fff;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            z-index: 2;
        }

        /* Colors */
        .bg-primary {
            background-color: #007bff !important;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-danger {
            background-color: #dc3545 !important;
        }

        .bg-custom-warning {
            background-color: #fd7e14 !important;
            color: #fff !important;
        }

        .timeline-content {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px 15px;
            position: relative;
        }

        .timeline-content::after {
            content: '';
            position: absolute;
            top: 10px;
            left: -6px;
            width: 10px;
            height: 10px;
            background: #fff;
            border-left: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            transform: rotate(45deg);
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .user-name {
            font-weight: bold;
            font-size: 1rem;
            color: #343a40;
        }

        .action-date {
            font-size: 0.8rem;
            color: #868e96;
        }

        .role-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 0.75rem;
            border-radius: 4px;
            background: #f1f3f5;
            color: #495057;
            margin-bottom: 6px;
        }

        .timeline-comment {
            margin-top: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 8px 12px;
            font-size: 0.9rem;
            color: #495057;
            border-radius: 0 4px 4px 0;
        }

        /* Scanner Styles */
        #reader {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }

        #reader video {
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
@endpush

@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn ($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
    $showDate = fn ($date) => $date
        ? $digits($date->copy()->locale($isEn ? 'en' : 'bn')->translatedFormat($isEn ? 'd M Y' : 'j F Y'))
        : '—';
    $isApprover = in_array(Auth::user()->role_id, [2, 3]);
    $card = 'tw-rounded-2xl tw-border tw-border-solid tw-border-slate-200/80 tw-bg-white tw-shadow-surface';
    $label = 'tw-mb-1.5 tw-block tw-text-[13px] tw-font-semibold tw-text-slate-600';
    $input = 'tw-block tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition placeholder:tw-text-slate-400 hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15';
    // Dialog (same building blocks as the requisition view dialog)
    $btn = 'tw-inline-flex tw-min-h-11 tw-select-none tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-px-4 tw-text-sm tw-font-semibold tw-leading-none tw-transition hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 active:tw-translate-y-px disabled:tw-cursor-not-allowed disabled:tw-opacity-50';
    $btnSecondary = $btn . ' tw-border-slate-300 tw-bg-white tw-text-slate-700 tw-shadow-sm hover:tw-bg-slate-50 hover:tw-text-slate-900 focus-visible:tw-ring-slate-400/30';
    $btnDanger = $btn . ' tw-border-rose-600 tw-bg-rose-600 tw-text-white tw-shadow-sm hover:tw-border-rose-700 hover:tw-bg-rose-700 hover:tw-text-white focus-visible:tw-ring-rose-500/30';
    $btnSuccess = $btn . ' tw-border-emerald-600 tw-bg-emerald-600 tw-text-white tw-shadow-sm hover:tw-border-emerald-700 hover:tw-bg-emerald-700 hover:tw-text-white focus-visible:tw-ring-emerald-500/30';
    $btnSmall = 'tw-inline-flex tw-min-h-9 tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-px-3 tw-text-sm tw-font-semibold tw-leading-none tw-transition focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 disabled:tw-cursor-not-allowed disabled:tw-opacity-60';
    $todayLabel = $digits(now()->locale($isEn ? 'en' : 'bn')->translatedFormat($isEn ? 'l, d M Y' : 'l, j F Y'));
    $pageBtn = 'tw-inline-flex tw-h-10 tw-min-w-[2.5rem] tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-px-2 tw-text-sm tw-font-medium tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 disabled:tw-cursor-not-allowed disabled:tw-opacity-40';
@endphp

@section('content')
    {{-- The list has the same look as the requisition list screen (Tailwind, scoped to .tw-root).
         Search, page size and paging run in the browser: every pending task is already on the page. --}}
    <div class="container-fluid">
        <div class="tw-root">
            <div class="tw-space-y-3 tw-pb-6 tw-pt-3 tw-font-sans tw-text-slate-800">

                {{-- ============ Page header ============ --}}
                <header class="{{ $card }} tw-relative tw-overflow-hidden tw-px-4 tw-py-4 sm:tw-px-6 sm:tw-py-5">
                    <div class="tw-absolute tw-inset-x-0 tw-top-0 tw-h-1 tw-bg-gradient-to-r tw-from-brand-600 tw-via-brand-500 tw-to-accent-500" aria-hidden="true"></div>
                    <div class="tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between">
                        <div class="tw-min-w-0">
                            <nav aria-label="breadcrumb" class="tw-mb-1 tw-text-xs tw-text-slate-500">
                                <a href="{{ route('home') }}" wire:navigate class="tw-text-slate-500 hover:tw-text-brand-600">{{ __('breadcrumb.home') }}</a>
                                <span class="tw-mx-1.5 tw-text-slate-300" aria-hidden="true">/</span>
                                <span aria-current="page">{{ __('requisition.pending_manage.title') }}</span>
                            </nav>
                            <h1 class="tw-m-0 tw-text-xl tw-font-bold tw-leading-snug tw-tracking-tight tw-text-slate-900 sm:tw-text-2xl">
                                {{ __('requisition.pending_title') }}
                            </h1>
                            {{-- Super Admin and Admin see the whole queue, not only what waits for them --}}
                            <p class="tw-mb-0 tw-mt-1 tw-text-sm tw-text-slate-500">{{ in_array((int) Auth::user()->role_id, [1, 2], true) ? __('requisition.pending_manage.subtitle_all') : __('requisition.pending_manage.subtitle') }}</p>
                        </div>

                        {{-- SCANNER TOOLS (ONLY FOR STORE KEEPER - Role 4) --}}
                        @if (Auth::user()->role_id == 4)
                            <div class="tw-flex tw-w-full tw-items-stretch sm:tw-w-80">
                                <label for="scan-qr-input" class="tw-sr-only">{{ __('requisition.pending_manage.scan_placeholder') }}</label>
                                <input type="text" id="scan-qr-input" autocomplete="off"
                                    class="{{ $input }} tw-rounded-r-none"
                                    placeholder="{{ __('requisition.pending_manage.scan_placeholder') }}">
                                <button type="button" id="btn-open-camera"
                                    class="tw--ml-px tw-inline-flex tw-min-h-11 tw-flex-none tw-items-center tw-justify-center tw-rounded-r-lg tw-border tw-border-solid tw-border-amber-400 tw-bg-amber-400 tw-px-3.5 tw-text-slate-900 tw-transition hover:tw-bg-amber-500 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-amber-400/40"
                                    title="{{ __('requisition.pending_manage.scan_camera') }}" aria-label="{{ __('requisition.pending_manage.scan_camera') }}">
                                    <i class="fas fa-camera" aria-hidden="true"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </header>

                {{-- ============ Filters: per page, search ============ --}}
                <section class="req-filters {{ $card }} tw-flex tw-flex-col tw-gap-3 tw-px-4 tw-py-3 sm:tw-flex-row sm:tw-items-end sm:tw-px-5"
                    aria-label="{{ __('requisition.manage.filters') }}">
                    <div class="sm:tw-w-28">
                        <label for="pending-per-page" class="{{ $label }} !tw-mb-1">{{ __('requisition.manage.per_page') }}</label>
                        <select id="pending-per-page" class="{{ $input }}">
                            @foreach ([10, 25, 50] as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tw-relative tw-min-w-0 tw-flex-1">
                        <label for="pending-search" class="tw-sr-only">{{ __('requisition.manage.search') }}</label>
                        <i class="fas fa-search tw-pointer-events-none tw-absolute tw-left-3.5 tw-top-1/2 tw--translate-y-1/2 tw-text-sm tw-text-slate-400" aria-hidden="true"></i>
                        <input id="pending-search" type="search" autocomplete="off"
                            placeholder="{{ __('requisition.pending_manage.search_placeholder') }}"
                            class="{{ $input }} tw-pl-10">
                    </div>
                </section>

                {{-- ============ List ============ --}}
                <section class="{{ $card }} tw-relative tw-overflow-hidden" aria-labelledby="pending-list-title" aria-live="polite">
                    <h2 id="pending-list-title" class="tw-sr-only">{{ __('requisition.pending_list') }}</h2>

                    @if ($pendingTasks->count())
                        <div class="tw-overflow-x-auto">
                            <table id="pending-req-table" class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-text-sm tw-font-semibold tw-uppercase tw-tracking-wide tw-text-slate-500">
                                        <th scope="col" class="tw-w-12 tw-whitespace-nowrap tw-px-2 tw-py-2 tw-text-center sm:tw-w-16 sm:tw-px-3">{{ __('commontable.serial') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 sm:tw-px-5">{{ __('requisition.pending_manage.requester_details') }}</th>
                                        <th scope="col" class="tw-hidden tw-whitespace-nowrap tw-px-5 tw-py-2 md:tw-table-cell">{{ __('requisition.manage.date') }}</th>
                                        <th scope="col" class="tw-hidden tw-px-5 tw-py-2 lg:tw-table-cell">{{ __('requisition.manage.items') }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-right sm:tw-px-5">{{ __('requisition.manage.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Newest requisition first --}}
                                    @foreach ($pendingTasks->sortByDesc(fn ($task) => $task->requisition->id) as $task)
                                        @php
                                            $req = $task->requisition;
                                            $requester = $req->requestedBy->name ?? __('requisition.pending_manage.unknown');
                                            // Section / department of the requester, in the page language.
                                            $department = $req->requestedBy?->department;
                                            $departmentName = $department
                                                ? (($isEn ? $department->name : ($department->name_bn ?: $department->name)) ?: $department->name)
                                                : __('requisition.pending_manage.no_department');
                                            $date = $showDate($req->requisition_date);
                                            // When the requisition was submitted (its creation time).
                                            $time = $req->created_at
                                                ? $digits($req->created_at->copy()->locale($isEn ? 'en' : 'bn')->translatedFormat($isEn ? 'h:i A' : 'g:i A'))
                                                : null;
                                            $itemsText = __('requisition.manage.items_summary', ['count' => $digits($req->items->count())]);
                                            $productNames = $req->items->pluck('product.name')->filter();
                                            $productsShort = $productNames->take(3)->join(', ') . ($productNames->count() > 3 ? ' …' : '');
                                            // Super Admin / Admin also see requisitions that wait for someone else: those are view-only.
                                            $canAct = $isApprover && (int) $task->to_user_id === (int) Auth::id();
                                            $actionLabel = $canAct ? __('requisition.pending_manage.view_action') : __('requisition.manage.view');
                                        @endphp
                                        <tr data-id="{{ $req->id }}"
                                            data-search="{{ mb_strtolower(implode(' ', [$req->id, $digits($req->id), $requester, $department?->name, $department?->name_bn, $date, $req->requisition_date?->format('d-M-Y Y-m-d'), $productNames->join(' ')])) }}"
                                            class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-transition hover:tw-bg-slate-50/70 last:tw-border-b-0">
                                            {{-- Serial number: set by the list script, so it follows the search and the page --}}
                                            <td class="pending-serial tw-whitespace-nowrap tw-px-2 tw-py-1 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($loop->iteration) }}</td>
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
                                                <span class="tw-block tw-truncate tw-text-sm tw-leading-tight tw-text-slate-500" title="{{ $productNames->join(', ') }}">{{ $productsShort }}</span>
                                            </td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-1 tw-text-right sm:tw-px-5">
                                                {{-- Opens the details / decision dialog below --}}
                                                <button type="button" class="view-action-btn tw-inline-flex tw-min-h-8 tw-items-center tw-justify-center tw-gap-2 tw-rounded-lg tw-border tw-border-solid tw-border-brand-200 tw-bg-brand-50 tw-px-3 tw-text-sm tw-font-semibold tw-text-brand-700 tw-transition hover:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25"
                                                    data-id="{{ $req->id }}"
                                                    data-can-act="{{ $canAct ? 1 : 0 }}"
                                                    data-url="{{ route('requisitions.get_details', $req->id) }}"
                                                    title="{{ $actionLabel }}"
                                                    aria-label="{{ $actionLabel }} #{{ $req->id }}">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                    <span class="tw-hidden xl:tw-inline">{{ $actionLabel }}</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Shown when the search matches nothing --}}
                        <p id="pending-no-match" class="tw-m-0 tw-hidden tw-px-6 tw-py-10 tw-text-center tw-text-sm tw-text-slate-500">
                            <i class="fas fa-search tw-mb-2 tw-block tw-text-xl tw-text-slate-300" aria-hidden="true"></i>
                            {{ __('requisition.manage.no_results_title') }}
                        </p>

                        {{-- Footer: count + pagination --}}
                        <div class="tw-flex tw-flex-col tw-items-center tw-gap-3 tw-border-0 tw-border-t tw-border-solid tw-border-slate-100 tw-px-4 tw-py-3 sm:tw-flex-row sm:tw-justify-between sm:tw-px-5">
                            <p id="pending-info" class="tw-m-0 tw-text-sm tw-text-slate-500"></p>
                            <nav id="pending-pager" role="navigation" aria-label="{{ __('commontable.pagination') }}" class="tw-flex tw-items-center tw-gap-1"
                                data-btn="{{ $pageBtn }}"></nav>
                        </div>
                    @else
                        <div class="tw-flex tw-flex-col tw-items-center tw-px-6 tw-py-14 tw-text-center">
                            <div class="tw-mb-4 tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-brand-50 tw-text-xl tw-text-brand-600" aria-hidden="true">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <h3 class="tw-m-0 tw-text-base tw-font-semibold tw-text-slate-900">{{ __('requisition.pending_manage.empty_title') }}</h3>
                            <p class="tw-mb-0 tw-mt-1 tw-max-w-sm tw-text-sm tw-text-slate-500">{{ __('requisition.pending_manage.empty_text') }}</p>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    {{-- ACTION MODAL: details, lot assignment and the decision.
         Bootstrap opens and closes it (the page script relies on that); the inside is built with the
         same Tailwind blocks as the requisition view dialog, so both dialogs look and read alike. --}}
    <div class="modal fade tw-root" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content tw-font-sans tw-text-slate-800">
                {{-- Header: title, status, today's date, close --}}
                <div class="tw-flex tw-flex-none tw-flex-wrap tw-items-start tw-justify-between tw-gap-x-4 tw-gap-y-1 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-3 sm:tw-flex-nowrap sm:tw-px-6">
                    <div class="tw-flex tw-min-w-0 tw-flex-1 tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1">
                        <h2 id="actionModalLabel" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900">
                            {{ __('requisition.pending_manage.modal.title_short') }} <span id="modal-req-no"></span>
                        </h2>
                        <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-whitespace-nowrap tw-rounded-full tw-bg-amber-50 tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-text-amber-800 tw-ring-1 tw-ring-inset tw-ring-amber-300">
                            <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-amber-500" aria-hidden="true"></span>{{ __('requisition.manage.status.pending') }}
                        </span>
                    </div>
                    <p class="tw-order-last tw-m-0 tw-flex tw-w-full tw-items-center tw-gap-2 tw-text-sm tw-font-medium tw-text-slate-600 sm:tw-order-none sm:tw-ml-auto sm:tw-w-auto sm:tw-self-center sm:tw-whitespace-nowrap"
                        title="{{ __('requisition.manage.today') }}">
                        <i class="fas fa-calendar-day tw-text-brand-600" aria-hidden="true"></i>
                        <span class="tw-sr-only">{{ __('requisition.manage.today') }}:</span>
                        <time datetime="{{ now()->format('Y-m-d') }}">{{ $todayLabel }}</time>
                    </p>
                    <button type="button" data-dismiss="modal" aria-label="{{ __('commontable.close') }}"
                        class="tw--mr-2 tw--mt-1 tw-inline-flex tw-h-11 tw-w-11 tw-flex-none tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-border-transparent tw-bg-slate-100 tw-text-slate-600 tw-transition hover:tw-bg-slate-200 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 md:tw-h-9 md:tw-w-9">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="tw-min-h-0 tw-flex-1 tw-space-y-4 tw-overflow-y-auto tw-overscroll-contain tw-px-5 tw-py-4 sm:tw-px-6">
                    <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-3">
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_on') }}</dt>
                            <dd id="modal-date" class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900"></dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_by') }}</dt>
                            <dd id="modal-requestor" class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900 tw-wrap-anywhere"></dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.items') }}</dt>
                            <dd id="modal-items-count" class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900"></dd>
                        </div>
                    </dl>

                    {{-- Items & assignment --}}
                    <div>
                        <div class="tw-mb-2 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2">
                            <h3 class="tw-m-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">{{ __('requisition.manage.item_list') }}</h3>

                            {{-- BUTTONS: ONLY FOR STORE ADMIN (3) --}}
                            @if (Auth::user()->role_id == 3)
                                <div class="tw-flex tw-flex-wrap tw-gap-2">
                                    {{-- Appears when rows are ticked: removes those items from the requisition --}}
                                    <button type="button" id="btn-delete-items" class="{{ $btnSmall }} tw-border-rose-300 tw-bg-white tw-text-rose-700 hover:tw-bg-rose-50" style="display: none;">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i> <span id="btn-delete-items-text"></span>
                                    </button>
                                    <button type="button" id="btn-split-notify" class="{{ $btnSmall }}" style="display: none;">
                                        <i class="fas fa-random" aria-hidden="true"></i> {{ __('requisition.pending_manage.modal.split') }}
                                    </button>
                                    <button type="button" id="btn-update-qty" class="{{ $btnSmall }}">
                                        <i class="fas fa-save" aria-hidden="true"></i> {{ __('requisition.pending_manage.modal.save_assignments') }}
                                    </button>
                                </div>
                            @endif
                        </div>

                        <form id="items-form" class="tw-m-0">
                            <div class="tw-overflow-x-auto tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                                <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                    <thead>
                                        <tr class="tw-bg-slate-50 tw-text-base tw-font-semibold tw-text-slate-600">
                                            <th scope="col" class="tw-w-12 tw-px-2 tw-py-2 tw-text-center sm:tw-w-20 sm:tw-whitespace-nowrap">{{ __('lot.table_sl') }}</th>
                                            {{-- Checkbox: Only for Store Admin (3) --}}
                                            @if (Auth::user()->role_id == 3)
                                                <th scope="col" class="tw-w-10 tw-px-2 tw-py-2 tw-text-center">
                                                    <input type="checkbox" id="select-all-items" class="tw-h-4 tw-w-4 tw-cursor-pointer"
                                                        title="{{ __('requisition.pending_manage.modal.select_all') }}" aria-label="{{ __('requisition.pending_manage.modal.select_all') }}">
                                                </th>
                                            @endif
                                            <th scope="col" class="tw-px-4 tw-py-2">{{ __('requisition.manage.product') }}</th>
                                            <th scope="col" class="tw-px-3 tw-py-2 tw-text-right sm:tw-whitespace-nowrap">{{ __('requisition.requested_quantity') }}</th>
                                            <th scope="col" class="tw-px-4 tw-py-2 tw-text-right sm:tw-whitespace-nowrap">{{ __('requisition.approved_quantity') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-items-body"></tbody>
                                </table>
                            </div>

                            {{-- More than ten items: the rest are on further pages (the rows stay in the form) --}}
                            <nav id="modal-items-pager" class="tw-mt-2 tw-hidden tw-flex-wrap tw-items-center tw-justify-between tw-gap-2" aria-label="{{ __('commontable.pagination') }}">
                                <p id="modal-items-info" class="tw-m-0 tw-text-xs tw-font-medium tw-text-slate-600" aria-live="polite"></p>
                                <div id="modal-items-pages" class="tw-flex tw-flex-wrap tw-gap-1"></div>
                            </nav>
                        </form>
                    </div>

                    {{-- Comment that goes with the decision --}}
                    <div>
                        <label for="action-comments" class="{{ $label }}">{{ __('requisition.approval_comments') }}</label>
                        <textarea id="action-comments" rows="2" class="{{ $input }} tw-resize-y disabled:tw-cursor-not-allowed disabled:tw-bg-slate-100"
                            placeholder="{{ __('requisition.enter_comments') }}"></textarea>
                    </div>

                    {{-- Approval history --}}
                    <div>
                        <h3 class="tw-mb-2.5 tw-mt-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">{{ __('requisition.approval_history') }}</h3>
                        <ol id="modal-timeline" class="tw-relative tw-m-0 tw-list-none tw-space-y-3 tw-border-0 tw-border-l-2 tw-border-solid tw-border-slate-200 tw-py-0 tw-pl-5 tw-pr-0"></ol>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="tw-flex tw-flex-none tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-2.5 sm:tw-flex-row sm:tw-justify-end sm:tw-px-6">
                    {{-- Print: Visible for Admin (2) & Store Admin (3) --}}
                    @if (in_array(Auth::user()->role_id, [2, 3]))
                        <a href="#" id="btn-print" target="_blank" rel="noopener" class="{{ $btnSecondary }} sm:tw-mr-auto" style="display: none;">
                            <i class="fas fa-print" aria-hidden="true"></i> {{ __('requisition.print_invoice') }}
                        </a>
                    @endif

                    <button type="button" data-dismiss="modal" class="{{ $btnSecondary }}">{{ __('commontable.close') }}</button>

                    {{-- Actions: Visible for Admin (2) & Store Admin (3) --}}
                    @if (in_array(Auth::user()->role_id, [2, 3]))
                        <button type="button" id="btn-reject" class="{{ $btnDanger }}">
                            <i class="fas fa-times" aria-hidden="true"></i> {{ __('buttons.reject') }}
                        </button>
                        <button type="button" id="btn-approve" class="{{ $btnSuccess }}">
                            <i class="fas fa-check" aria-hidden="true"></i> {{ __('buttons.approve') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SCANNER MODAL (Only for Store Keeper) --}}
    @if (Auth::user()->role_id == 4)
        <div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-labelledby="scannerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-heading">
                            <h5 class="modal-title" id="scannerModalLabel"><i class="fas fa-camera" aria-hidden="true"></i> {{ __('requisition.scan_qr') }}</h5>
                            <p class="modal-hint">{{ __('requisition.pending_manage.modal.scan_hint') }}</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('commontable.close') }}"
                            onclick="stopScanner()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="reader" style="width: 100%;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="stopScanner()"
                            data-dismiss="modal">{{ __('buttons.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Hidden Forms --}}
    <form id="approve-form" method="POST" style="display:none;">@csrf</form>
    <form id="reject-form" method="POST" style="display:none;">@csrf</form>
@endsection

@if (Auth::user()->role_id == 4)
    @push('head_scripts')
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    @endpush
@endif

@push('js')
    {{-- NEW: Catch backend Flash messages and display them in Toastr --}}
    <script>
        $(document).ready(function() {
            // Check for Laracasts Flash messages
            @if (session()->has('flash_notification'))
                @foreach (session('flash_notification', collect()) as $message)
                    let flashLevel = "{{ $message['level'] }}";
                    let flashMsg = "{!! $message['message'] !!}";

                    if (flashLevel === 'danger' || flashLevel === 'error') {
                        toastr.error(flashMsg);
                    } else if (flashLevel === 'success') {
                        toastr.success(flashMsg);
                    } else if (flashLevel === 'warning') {
                        toastr.warning(flashMsg);
                    } else {
                        toastr.info(flashMsg);
                    }
                @endforeach
                {{ session()->forget('flash_notification') }}
            @endif

            // Check for standard Laravel session errors (just in case)
            @if (session('error'))
                toastr.error("{{ session('error') }}");
            @endif
            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @endif
        });
    </script>
    <script>
        var html5QrcodeScanner = null;

        // --- SCANNER LOGIC (Store Keeper) ---
        function onScanSuccess(decodedText, decodedResult) {
            let cleanVal = decodedText.split('?')[0];
            let parts = cleanVal.split('/');
            let id = parts[parts.length - 1];

            if ($.isNumeric(id)) {
                if (window.pendingList) {
                    window.pendingList.search(id);
                }
                toastr.success(@json(__('requisition.pending_manage.modal.scan_found')).replace(':id', id));
                stopScanner();
                $('#scannerModal').modal('hide');
                processRequisitionViaScan(id);
            } else {
                toastr.warning(@json(__('requisition.pending_manage.modal.scan_invalid')));
            }
        }

        function processRequisitionViaScan(id) {
            Swal.fire({
                title: @json(__('requisition.pending_manage.modal.scan_processing')).replace(':id', id),
                text: @json(__('requisition.pending_manage.modal.scan_processing_text')),
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                willOpen: () => {
                    Swal.showLoading();
                    $.ajax({
                        url: "{{ url('/requisitions') }}/" + id + "/verify-scan",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            Swal.fire({
                                    icon: 'success',
                                    title: @json(__('sweetalert.success')),
                                    text: res.message,
                                    timer: 2000
                                })
                                .then(() => {
                                    location.reload();
                                });
                        },
                        error: function(err) {
                            let msg = err.responseJSON ? err.responseJSON.message :
                                @json(__('requisition.pending_manage.modal.scan_failed'));
                            Swal.fire(@json(__('sweetalert.error_title')), msg, 'error');
                        }
                    });
                }
            });
        }

        function onScanFailure(error) {}

        function startScanner() {
            $('#scannerModal').modal('show');
            if (html5QrcodeScanner === null) {
                if (typeof Html5QrcodeScanner !== 'undefined') {
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    }, false);
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                } else {
                    console.error("Scanner library not loaded.");
                }
            }
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(_ => {
                    html5QrcodeScanner = null;
                }).catch(error => {
                    console.error("Failed to clear.");
                });
            }
        }

        $(document).ready(function() {
            // ---- List: search, page size and paging in the browser (all rows are on the page) ----
            window.pendingList = (function() {
                var rows = $('#pending-req-table tbody tr').get();
                var useBangla = @json(app()->getLocale() !== 'en');
                var infoText = @json(__('requisition.manage.showing'));
                var labels = {
                    previous: @json(__('requisition.manage.previous_page')),
                    next: @json(__('requisition.manage.next_page'))
                };
                var pager = $('#pending-pager');
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
                    // Serial numbers follow the search and the page.
                    visible.forEach(function(row, index) {
                        var cell = row.querySelector('.pending-serial');
                        if (cell) { cell.textContent = digits(start + index + 1); }
                    });
                    $('#pending-no-match').toggleClass('tw-hidden', matched.length !== 0);
                    $('#pending-req-table').closest('div').toggleClass('tw-hidden', matched.length === 0);

                    $('#pending-info').text(matched.length === 0 ? '' : infoText
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
                $('#pending-per-page').on('change', function() {
                    state.perPage = parseInt($(this).val(), 10) || 10;
                    state.page = 1;
                    render();
                });
                $('#pending-search').on('input', function() {
                    state.term = this.value;
                    state.page = 1;
                    render();
                });
                render();

                return {
                    // Used by the QR scanner: show the scanned requisition.
                    search: function(term) {
                        state.term = String(term);
                        state.page = 1;
                        $('#pending-search').val(state.term);
                        render();
                    }
                };
            })();
            let currentRequisitionId = null;
            let totalItemsInModal = 0;

            // Colour states of the two Store Admin buttons in the dialog.
            const saveCalm = 'tw-border-amber-300 tw-bg-amber-50 tw-text-amber-800 hover:tw-bg-amber-100';
            const saveDirty = 'tw-border-rose-300 tw-bg-rose-50 tw-text-rose-700 hover:tw-bg-rose-100';
            const splitRed = 'tw-border-rose-600 tw-bg-rose-600 tw-text-white hover:tw-bg-rose-700';
            const splitBlue = 'tw-border-sky-600 tw-bg-sky-600 tw-text-white hover:tw-bg-sky-700';
            $('#btn-split-notify').addClass(splitRed);

            // Texts of the details dialog, in the page language.
            const T = @json(__('requisition.pending_manage.modal'));
            const useBanglaDigits = @json(app()->getLocale() !== 'en');
            const dateLocale = useBanglaDigits ? 'bn-BD' : 'en-GB';

            function localDigits(value) {
                const text = String(value);
                return useBanglaDigits ? text.replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d]) : text;
            }

            // Text that goes into HTML built here is escaped first.
            function esc(value) {
                return $('<div>').text(value == null ? '' : String(value)).html();
            }

            // ISO date from the server, written in the page language; falls back to the server's own text.
            function showDate(iso, fallback, withTime) {
                const date = iso ? new Date(iso.length === 10 ? iso + 'T00:00:00' : iso) : null;
                if (!date || isNaN(date)) return fallback || '';
                const options = withTime ?
                    { day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: '2-digit' } :
                    { day: 'numeric', month: 'long', year: 'numeric' };
                return new Intl.DateTimeFormat(dateLocale, options).format(date);
            }

            // Flag to track if user touched inputs
            let hasUnsavedChanges = false;

            // Roles
            const isStoreAdmin = {{ Auth::user()->role_id == 3 ? 'true' : 'false' }};
            const isStoreKeeper = {{ Auth::user()->role_id == 4 ? 'true' : 'false' }};
            const isApprover = {{ in_array(Auth::user()->role_id, [2, 3]) ? 'true' : 'false' }};

            // --- BIND SCANNER EVENTS (Only for Store Keeper) ---
            if (isStoreKeeper) {
                $('#btn-open-camera').click(function() {
                    startScanner();
                });
                $('#scannerModal').on('hidden.bs.modal', function() {
                    stopScanner();
                });

                $('#scan-qr-input').on('keyup', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        let val = $(this).val();
                        if (!val) return;

                        let cleanVal = val.split('?')[0];
                        let parts = cleanVal.split('/');
                        let id = parts[parts.length - 1];

                        if ($.isNumeric(id)) {
                            $(this).val('');
                            processRequisitionViaScan(id);
                        } else {
                            toastr.error(T.scan_invalid);
                        }
                    }
                });
            }

            // ---- Items of the dialog: ten per page ----
            // Every row stays in the table (only hidden), so saving, splitting and deleting
            // still see the quantities and ticks of all pages.
            const ITEMS_PER_PAGE = 10;
            let itemsPage = 1;
            const itemsInfoText = @json(__('commontable.dt_info'));
            const pageBtnBase = 'tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-px-2 tw-text-xs tw-font-semibold tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40';
            const pageBtnIdle = pageBtnBase + ' tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50';
            const pageBtnActive = pageBtnBase + ' tw-border-brand-600 tw-bg-brand-600 tw-text-white';

            function renderItemsPage() {
                const rows = $('#modal-items-body tr.modal-item-row');
                const total = rows.length;
                const last = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));
                itemsPage = Math.min(Math.max(1, itemsPage), last);
                const start = (itemsPage - 1) * ITEMS_PER_PAGE;

                rows.each(function(index) {
                    $(this).toggle(index >= start && index < start + ITEMS_PER_PAGE);
                });

                const pager = $('#modal-items-pager');
                pager.toggleClass('tw-hidden', last <= 1).toggleClass('tw-flex', last > 1);
                if (last <= 1) return;

                $('#modal-items-info').text(itemsInfoText
                    .replace('_TOTAL_', localDigits(total))
                    .replace('_START_', localDigits(start + 1))
                    .replace('_END_', localDigits(Math.min(start + ITEMS_PER_PAGE, total))));

                const pages = $('#modal-items-pages').empty();
                const add = (html, page, options) => {
                    const button = $('<button type="button">').html(html).addClass(options.active ? pageBtnActive : pageBtnIdle)
                        .attr('data-page', page).prop('disabled', !!options.disabled);
                    if (options.label) button.attr('aria-label', options.label).attr('title', options.label);
                    if (options.active) button.attr('aria-current', 'page');
                    pages.append(button);
                };
                add('<i class="fas fa-chevron-left tw-text-xs" aria-hidden="true"></i>', itemsPage - 1, { disabled: itemsPage === 1, label: @json(__('commontable.dt_previous')) });
                for (let page = 1; page <= last; page++) {
                    add(localDigits(page), page, { active: page === itemsPage });
                }
                add('<i class="fas fa-chevron-right tw-text-xs" aria-hidden="true"></i>', itemsPage + 1, { disabled: itemsPage === last, label: @json(__('commontable.dt_next')) });
            }

            $('#modal-items-pages').on('click', 'button', function() {
                itemsPage = parseInt($(this).attr('data-page'), 10) || 1;
                renderItemsPage();
            });

            // --- VIEW MODAL ---
            $('#pending-req-table tbody').on('click', '.view-action-btn', function() {
                let url = $(this).data('url');
                currentRequisitionId = $(this).data('id');

                $('#modal-req-no').text('#' + localDigits(currentRequisitionId));
                $('#actionModal').modal('show');
                $('#btn-split-notify, #btn-delete-items').hide();
                $('#select-all-items').prop('checked', false).prop('indeterminate', false);

                hasUnsavedChanges = false;
                updateSaveButtonUI();

                // A requisition that waits for another approver is view-only: no decision buttons.
                const canAct = String($(this).data('can-act')) === '1';
                $('#btn-approve, #btn-reject').toggle(canAct);

                // Read-Only for Store Keeper (Role 4) and for view-only requisitions
                if (isStoreKeeper || !canAct) {
                    $('#action-comments').prop('disabled', true).val('');
                } else {
                    $('#action-comments').prop('disabled', false).val('');
                }

                $('#modal-timeline').html('<li class="tw-text-sm tw-text-slate-500">' + esc(T.loading) + '</li>');
                $('#modal-items-body').html(
                    '<tr><td colspan="5" class="tw-px-4 tw-py-6 tw-text-center tw-text-sm tw-text-slate-500">' + esc(T.loading_items) + '</td></tr>');
                $('#modal-requestor, #modal-date, #modal-items-count').text('…');
                $('#modal-items-pager').addClass('tw-hidden').removeClass('tw-flex');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(showDate(res.date_iso, res.formatted_date, false));
                        totalItemsInModal = res.requisition.items.length;
                        if (!isStoreKeeper) {
                            let printUrl = "{{ route('requisitions.print', ':id') }}".replace(
                                ':id', currentRequisitionId);
                            $('#btn-print').attr('href', printUrl);
                            if (res.is_print_enabled === true) {
                                $('#btn-print').fadeIn(); // Show button
                            } else {
                                $('#btn-print').hide(); // Ensure it stays hidden
                            }
                        }
                        $('#modal-items-count').text(T.items_count.replace(':count', localDigits(totalItemsInModal)));

                        // ---- Item rows (same row style as the requisition view dialog) ----
                        const cell = 'tw-px-3 tw-py-1.5';
                        const fieldBase = 'tw-min-h-9 tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15';
                        let itemsHtml = '';
                        res.requisition.items.forEach((item, itemIndex) => {
                            // Product Unit
                            let unit = item.product.product_unit || '';
                            // Lots are assigned automatically on approval; the Store Admin only needs
                            // to know when a product is out of stock (those items can be split off).
                            const available = item.available_quantity != null ? Number(item.available_quantity) : null;
                            const isUnavailable = isStoreAdmin && available !== null && available <= 0;
                            const isShort = isStoreAdmin && ! isUnavailable && available !== null && available < Number(item.quantity);
                            let stockNote = '';
                            if (isUnavailable) {
                                stockNote = `<span class="stock-unavailable tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-semibold tw-text-rose-700"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-rose-500" aria-hidden="true"></span>${esc(T.stock_unavailable)}</span>`;
                            } else if (isShort) {
                                stockNote = `<span class="tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-semibold tw-text-amber-700"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-amber-500" aria-hidden="true"></span>${esc(T.stock_short.replace(':qty', localDigits(available)))}</span>`;
                            }

                            let subCatName = '';
                            if (item.product.sub_category) subCatName = item.product.sub_category.name;
                            else if (item.product.subCategory) subCatName = item.product.subCategory.name;
                            let categoryDisplay = esc(item.product.category ? item.product.category.name : '');
                            if (subCatName) categoryDisplay += ' · ' + esc(subCatName);

                            // Checkbox Logic (Role 3 Only)
                            let checkboxHtml = '';
                            if (isStoreAdmin) {
                                // Every row can be ticked (to delete it); only out-of-stock rows can be split off.
                                checkboxHtml =
                                    `<td class="tw-px-2 tw-py-1.5 tw-text-center"><input type="checkbox" class="split-item-checkbox tw-h-4 tw-w-4 tw-cursor-pointer" value="${item.id}" data-unavailable="${isUnavailable ? 1 : 0}" aria-label="${esc(T.select_item)}: ${esc(item.product.name)}"></td>`;
                            }

                            // Approved quantity: a field for the Store Admin, plain text for everyone else.
                            // The unit follows the number in both cases.
                            const unitHtml = unit ? `<span class="tw-text-sm tw-text-slate-500">${esc(unit)}</span>` : '';
                            let approvedHtml = isStoreAdmin ?
                                `<span class="tw-inline-flex tw-items-center tw-justify-end tw-gap-2"><input type="number" class="item-qty-input tw-block tw-w-20 tw-px-2 tw-py-1 tw-text-center tw-font-semibold tw-tabular-nums ${fieldBase}" data-id="${item.id}" value="${item.quantity}" min="1" aria-label="${esc(T.approved_quantity)}: ${esc(item.product.name)}">${unitHtml}</span>` :
                                `<span class="tw-font-semibold tw-tabular-nums tw-text-slate-900">${localDigits(item.quantity)}</span> ${unitHtml}`;

                            itemsHtml += `
                            <tr class="modal-item-row tw-border-0 tw-border-t tw-border-solid tw-border-slate-100">
                                <td class="tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600">${localDigits(itemIndex + 1)}</td>
                                ${checkboxHtml}
                                <td class="tw-px-4 tw-py-1.5">
                                    <span class="tw-block tw-break-words tw-font-medium tw-leading-tight tw-text-slate-900">${esc(item.product.name)}</span>
                                    <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500">${categoryDisplay}</span>
                                    ${stockNote}
                                </td>
                                <td class="tw-whitespace-nowrap ${cell} tw-text-right tw-tabular-nums">
                                    <span class="tw-font-semibold tw-text-slate-900">${localDigits(item.original_quantity || item.quantity)}</span>
                                    ${unitHtml}
                                </td>
                                <td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-right">${approvedHtml}</td>
                            </tr>`;
                        });
                        $('#modal-items-body').html(itemsHtml);
                        itemsPage = 1;
                        renderItemsPage();

                        if (isStoreAdmin) evaluateApproveButtonFromUI();

                        // ---- Timeline (same look as the requisition view dialog) ----
                        const dotColor = {
                            initiated: 'tw-bg-brand-500',
                            approved: 'tw-bg-emerald-500',
                            processed: 'tw-bg-emerald-500',
                            rejected: 'tw-bg-rose-500',
                            pending: 'tw-bg-amber-400'
                        };
                        let timelineHtml = '';
                        res.timeline.forEach(item => {
                            // The server sends English labels; show them in the page language.
                            const raw = item.status_raw || '';
                            const stepTitle = T.status[raw] || item.title;
                            const who = raw === 'pending' && item.pending_users ?
                                T.waiting_for.replace(':name', item.pending_users) :
                                T.by.replace(':name', item.user);
                            const stepRole = raw === 'initiated' ? T.requestor_role : item.role;
                            const stepDate = showDate(item.date_iso, item.date, true);
                            const commentHtml = item.comments ?
                                `<p class="tw-mb-0 tw-mt-1.5 tw-rounded-lg tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-sm tw-text-slate-700">${esc(item.comments)}</p>` :
                                '';
                            timelineHtml += `
                            <li class="tw-relative">
                                <span class="tw-absolute tw--left-[27px] tw-top-1 tw-flex tw-h-3 tw-w-3 tw-rounded-full tw-ring-4 tw-ring-white ${dotColor[raw] || 'tw-bg-brand-500'}" aria-hidden="true"></span>
                                <p class="tw-m-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-900">${esc(stepTitle)}</p>
                                <p class="tw-m-0 tw-text-xs tw-leading-snug tw-text-slate-500">${esc(who)} (${esc(stepRole)}) · ${esc(stepDate)}</p>
                                ${commentHtml}
                            </li>`;
                        });
                        $('#modal-timeline').html(timelineHtml);
                    },
                    error: function() {
                        toastr.error(T.load_failed);
                    }
                });
            });

            // --- STORE ADMIN SPECIFIC ACTIONS (Role 3) ---
            if (isStoreAdmin) {
                // Ticked rows: "delete" is offered for any selection, "split / notify" only when
                // every ticked item is out of stock (that is what splitting is for).
                function refreshSelectionButtons() {
                    const boxes = $('.split-item-checkbox');
                    const checked = boxes.filter(':checked');
                    const checkedCount = checked.length;
                    const allUnavailable = checkedCount > 0 &&
                        checked.filter((_, el) => String($(el).data('unavailable')) === '1').length === checkedCount;

                    $('#select-all-items')
                        .prop('checked', boxes.length > 0 && checkedCount === boxes.length)
                        .prop('indeterminate', checkedCount > 0 && checkedCount < boxes.length);

                    const deleteBtn = $('#btn-delete-items');
                    if (checkedCount > 0) {
                        $('#btn-delete-items-text').text(T.delete_selected.replace(':count', localDigits(checkedCount)));
                        deleteBtn.show();
                    } else {
                        deleteBtn.hide();
                    }

                    const btn = $('#btn-split-notify');
                    if (allUnavailable) {
                        btn.show();
                        if (checkedCount === totalItemsInModal) {
                            btn.removeClass(splitRed).addClass(splitBlue).html(
                                '<i class="fas fa-bell" aria-hidden="true"></i> ' + esc(T.notify_only));
                        } else {
                            btn.removeClass(splitBlue).addClass(splitRed).html(
                                '<i class="fas fa-random" aria-hidden="true"></i> ' + esc(T.split));
                        }
                    } else {
                        btn.hide();
                    }
                }

                $(document).on('change', '.split-item-checkbox', refreshSelectionButtons);
                $('#select-all-items').on('change', function() {
                    $('.split-item-checkbox').prop('checked', this.checked);
                    refreshSelectionButtons();
                });

                // Delete the ticked items
                $('#btn-delete-items').on('click', function() {
                    const ids = $('.split-item-checkbox:checked').map((_, el) => $(el).val()).get();
                    if (ids.length === 0) return;
                    if (ids.length >= totalItemsInModal) {
                        toastr.warning(T.delete_all_blocked);
                        return;
                    }
                    const url = "{{ route('requisitions.remove_items', ':id') }}".replace(':id', currentRequisitionId);

                    Swal.fire({
                        title: T.delete_title.replace(':count', localDigits(ids.length)),
                        text: T.delete_text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: T.delete_yes,
                        cancelButtonText: T.cancel
                    }).then((res) => {
                        if (!res.isConfirmed) return;
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                item_ids: ids
                            },
                            success: function(response) {
                                toastr.success(response.message);
                                $('#actionModal').modal('hide');
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : T.action_failed);
                            }
                        });
                    });
                });

                // Split Action
                $('#btn-split-notify').click(function() {
                    let selectedItemIds = [];
                    $('.split-item-checkbox:checked').each(function() {
                        selectedItemIds.push($(this).val());
                    });
                    if (selectedItemIds.length === 0) return;

                    let isFull = (selectedItemIds.length === totalItemsInModal);
                    let title = isFull ? T.notify_title : T.split_title;
                    let text = isFull ? T.notify_text : T.split_text;
                    let btnText = isFull ? T.notify_yes : T.split_yes;
                    let url = "{{ route('requisitions.split', ':id') }}".replace(':id',
                        currentRequisitionId);

                    Swal.fire({
                            title: title,
                            text: text,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            confirmButtonText: btnText,
                            cancelButtonText: T.cancel
                        })
                        .then((res) => {
                            if (res.isConfirmed) {
                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        item_ids: selectedItemIds
                                    },
                                    success: function(response) {
                                        toastr.success(response.message);
                                        $('#actionModal').modal('hide');
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1000);
                                    },
                                    error: function(xhr) {
                                        toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : T.action_failed);
                                    }
                                });
                            }
                        });
                });

                // DETECT CHANGES TO INPUTS
                $(document).on('change input', '.item-lot-select, .item-qty-input', function() {
                    hasUnsavedChanges = true;
                    evaluateApproveButtonFromUI();
                    updateSaveButtonUI();
                });

                // Update Qty
                $('#btn-update-qty').click(function() {
                    let itemsData = {};
                    $('.item-qty-input').each(function() {
                        let id = $(this).data('id');
                        let lotSelect = $(`.item-lot-select[data-id="${id}"]`);
                        itemsData[id] = {
                            quantity: $(this).val(),
                            lot_detail_id: lotSelect.length ? lotSelect.val() : null
                        };
                    });
                    let url = "{{ route('requisitions.update_items', ':id') }}".replace(':id',
                        currentRequisitionId);
                    let btn = $(this);
                    btn.text(T.saving).prop('disabled', true);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            items: itemsData
                        },
                        success: function(res) {
                            toastr.success(T.assignments_saved);
                            // RESET DIRTY FLAG ON SUCCESS
                            hasUnsavedChanges = false;
                            updateSaveButtonUI();
                            evaluateApproveButtonFromUI();
                        },
                        error: function() {
                            toastr.error(T.assignments_failed);
                        },
                        complete: function() {
                            btn.prop('disabled', false);
                            updateSaveButtonUI();
                        }
                    });
                });

                $(document).on('change', '.item-lot-select', function() {
                    evaluateApproveButtonFromUI();
                });
            }

            // --- APPROVER ACTIONS (Role 2 & 3) ---
            if (isApprover) {
                // Approve
                $('#btn-approve').click(function() {
                    let url = "{{ route('requisitions.approve', ':id') }}".replace(':id',
                        currentRequisitionId);
                    let comments = $('#action-comments').val();

                    // Store Admin Validation (Must Assign)
                    if (isStoreAdmin) {
                        if ($('.stock-unavailable').length > 0) {
                            toastr.warning(T.unavailable_block);
                            return;
                        }
                    }

                    Swal.fire({
                        title: T.approve_title,
                        text: T.approve_text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: T.approve_yes,
                        cancelButtonText: T.cancel
                    }).then((res) => {
                        if (res.isConfirmed) {
                            let form = $('#approve-form');
                            form.attr('action', url);
                            // .val() keeps quotes in the comment from breaking the field
                            form.html(`@csrf`).append($('<input type="hidden" name="comments">').val(comments));
                            $('#wait').show();
                            form.submit();
                        }
                    });
                });

                // Reject
                $('#btn-reject').click(function() {
                    let url = "{{ route('requisitions.reject', ':id') }}".replace(':id',
                        currentRequisitionId);
                    let comments = $('#action-comments').val();
                    if (!comments || !comments.trim()) {
                        toastr.error(T.reject_reason_required);
                        $('#action-comments').trigger('focus');
                        return;
                    }
                    Swal.fire({
                        title: T.reject_title,
                        text: T.reject_text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: T.reject_yes,
                        cancelButtonText: T.cancel
                    }).then((res) => {
                        if (res.isConfirmed) {
                            let form = $('#reject-form');
                            form.attr('action', url);
                            form.html(`@csrf`).append($('<input type="hidden" name="comments">').val(comments));
                            $('#wait').show();
                            form.submit();
                        }
                    });
                });
            }
            // Helper to update Save Button Appearance
            function updateSaveButtonUI() {
                if (!isStoreAdmin) return;
                const btn = $('#btn-update-qty');
                if (hasUnsavedChanges) {
                    btn.removeClass(saveCalm).addClass(saveDirty);
                    btn.html('<i class="fas fa-save" aria-hidden="true"></i> ' + esc(T.save_assignments) + ' *');
                } else {
                    btn.removeClass(saveDirty).addClass(saveCalm);
                    btn.html('<i class="fas fa-save" aria-hidden="true"></i> ' + esc(T.save_assignments));
                }
            }

            function evaluateApproveButtonFromUI() {
                if (!isStoreAdmin) return;
                let canApprove = true;
                if ($('.stock-unavailable').length > 0) canApprove = false;
                $('.item-lot-select').each(function() {
                    if (!$(this).val()) canApprove = false;
                });
                const btn = $('#btn-approve');
                if (canApprove) {
                    btn.prop('disabled', false).attr('title', T.ready_to_approve);
                } else {
                    btn.prop('disabled', true).attr('title', T.resolve_first);
                }
            }
        });
    </script>
@endpush
