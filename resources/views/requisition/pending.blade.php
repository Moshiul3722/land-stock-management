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
    <style>
        /* ---------- Scanner dialog ---------- */
        #scannerModal .scan-dialog { max-width: 30rem; }
        #scannerModal .modal-content { overflow: hidden; border: 0; border-radius: 1rem; box-shadow: 0 24px 60px -20px rgba(15, 23, 42, 0.45); }
        #scannerModal .scan-header { align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-bottom: 1px solid #eef2f7; }
        #scannerModal .scan-header-icon { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: linear-gradient(135deg, #1d65db, #4f46e5); color: #fff; font-size: 1.25rem; box-shadow: 0 8px 16px -8px rgba(37, 99, 235, 0.7); }
        #scannerModal .scan-header .modal-title { font-size: 1.125rem; }
        /* A narrow dialog: today's date sits on its own line under the title */
        #scannerModal .scan-header { flex-wrap: wrap; }
        #scannerModal .scan-header .modal-today { order: 3; flex: 1 1 100%; margin: 0.15rem 0 0; white-space: normal; }
        #scannerModal .scan-header .close { margin-left: auto; }
        #scannerModal .scan-header .modal-hint { font-size: 0.8125rem; line-height: 1.45; }
        #scannerModal .scan-body { padding: 1rem 1.25rem 1.1rem; }

        /* Camera | File */
        #scannerModal .scan-tabs { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-bottom: 0.9rem; padding: 4px; border-radius: 0.75rem; background: #f1f5f9; }
        #scannerModal .scan-tab { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; min-height: 2.5rem; padding: 0 0.75rem; border: 0; border-radius: 0.55rem; background: transparent; color: #64748b; font-size: 0.9375rem; font-weight: 600; cursor: pointer; transition: background-color 0.15s, color 0.15s, box-shadow 0.15s; }
        #scannerModal .scan-tab:hover { color: #0f172a; }
        #scannerModal .scan-tab.is-active { background: #fff; color: #1d4fd8; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.14); }
        #scannerModal .scan-tab:focus-visible { outline: 0; box-shadow: 0 0 0 3px rgba(47, 124, 246, 0.35); }

        /* Camera view */
        #scannerModal .scan-view { position: relative; display: flex; align-items: center; justify-content: center; min-height: 17rem; overflow: hidden; border-radius: 0.9rem; background: radial-gradient(circle at 50% 35%, #1e2b55, #0b1430 75%); }
        #scannerModal #reader { width: 100%; border: 0 !important; }
        #scannerModal #reader video { display: block; width: 100% !important; border-radius: 0.9rem; }
        #scannerModal .scan-view[data-state="idle"] #reader,
        #scannerModal .scan-view[data-state="starting"] #reader { display: none; }
        #scannerModal .scan-idle { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 0.65rem; padding: 1.5rem 1.25rem; text-align: center; }
        #scannerModal .scan-view[data-state="live"] .scan-idle { display: none; }
        #scannerModal .scan-idle-icon { display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: 999px; background: rgba(255, 255, 255, 0.1); color: #dbe5fb; font-size: 1.4rem; }
        #scannerModal .scan-idle-text { max-width: 20rem; margin: 0; color: #cbd5e1; font-size: 0.9rem; font-weight: 500; line-height: 1.5; }
        #scannerModal .scan-view[data-state="error"] .scan-idle-text { color: #fecaca; }
        #scannerModal .scan-view[data-state="starting"] #scan-camera-start { display: none; }

        /* Corner marks and a moving line while the camera is on */
        #scannerModal .scan-frame { position: absolute; top: 50%; left: 50%; z-index: 1; display: none; width: min(62%, 15rem); aspect-ratio: 1; transform: translate(-50%, -50%); pointer-events: none; }
        #scannerModal .scan-view[data-state="live"] .scan-frame { display: block; }
        #scannerModal .scan-frame span { position: absolute; width: 2rem; height: 2rem; border: 3px solid #fff; }
        #scannerModal .scan-frame span:nth-child(1) { top: 0; left: 0; border-right: 0; border-bottom: 0; border-top-left-radius: 0.6rem; }
        #scannerModal .scan-frame span:nth-child(2) { top: 0; right: 0; border-bottom: 0; border-left: 0; border-top-right-radius: 0.6rem; }
        #scannerModal .scan-frame span:nth-child(3) { bottom: 0; left: 0; border-top: 0; border-right: 0; border-bottom-left-radius: 0.6rem; }
        #scannerModal .scan-frame span:nth-child(4) { right: 0; bottom: 0; border-top: 0; border-left: 0; border-bottom-right-radius: 0.6rem; }
        #scannerModal .scan-frame i { position: absolute; right: 6%; left: 6%; top: 8%; height: 2px; border-radius: 2px; background: linear-gradient(90deg, transparent, #60a5fa, transparent); box-shadow: 0 0 12px 2px rgba(96, 165, 250, 0.7); animation: scan-line 2.2s ease-in-out infinite; }
        @keyframes scan-line { 0%, 100% { top: 8%; } 50% { top: 90%; } }
        @media (prefers-reduced-motion: reduce) { #scannerModal .scan-frame i { animation: none; top: 50%; } }
        #scannerModal .scan-live-hint { position: absolute; right: 0; bottom: 0.6rem; left: 0; z-index: 2; display: none; margin: 0; color: #fff; font-size: 0.8125rem; font-weight: 600; text-align: center; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.7); }
        #scannerModal .scan-view[data-state="live"] .scan-live-hint { display: block; }

        /* Buttons */
        #scannerModal .scan-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; min-height: 2.5rem; padding: 0 1rem; border: 1px solid transparent; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 600; line-height: 1; cursor: pointer; transition: background-color 0.15s, border-color 0.15s, box-shadow 0.15s, color 0.15s; }
        #scannerModal .scan-btn-primary { border-color: #1d65db; background: #1d65db linear-gradient(to bottom, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0)); color: #fff; box-shadow: 0 6px 14px -6px rgba(37, 99, 235, 0.7); }
        #scannerModal .scan-btn-primary:hover { border-color: #1a51b0; background-color: #1a51b0; }
        #scannerModal .scan-btn-quiet { width: 100%; margin-top: 0.65rem; border-color: #e2e8f0; background: #fff; color: #475569; }
        #scannerModal .scan-btn-quiet:hover { background: #f8fafc; color: #0f172a; }
        #scannerModal .scan-btn:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.3); }
        #scannerModal .scan-btn:disabled { opacity: 0.55; cursor: not-allowed; }

        /* File drop area */
        #scannerModal .scan-drop { display: flex; flex-direction: column; align-items: center; gap: 0.35rem; min-height: 17rem; justify-content: center; padding: 1.5rem 1.25rem; border: 2px dashed #cbd5e1; border-radius: 0.9rem; background: #f8fafc; text-align: center; transition: border-color 0.15s, background-color 0.15s; }
        #scannerModal .scan-drop.is-over { border-color: #2f7cf6; background: #eef4ff; }
        #scannerModal .scan-drop-icon { display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; margin-bottom: 0.3rem; border-radius: 999px; background: #e0eaff; color: #1d65db; font-size: 1.5rem; }
        #scannerModal .scan-drop-title { margin: 0; color: #0f172a; font-size: 0.975rem; font-weight: 600; }
        #scannerModal .scan-drop-or { margin: 0; color: #94a3b8; font-size: 0.8125rem; }
        #scannerModal .scan-drop-types { margin: 0.35rem 0 0; color: #64748b; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em; }
        #scannerModal .scan-file-status { margin: 0.5rem 0 0; color: #1d4fd8; font-size: 0.875rem; font-weight: 600; }

        #scannerModal .scan-note { display: flex; align-items: flex-start; gap: 0.5rem; margin: 0.85rem 0 0; padding: 0.6rem 0.75rem; border-radius: 0.6rem; background: #fffbeb; color: #92400e; font-size: 0.8125rem; line-height: 1.5; }
        #scannerModal .scan-note > i { margin-top: 0.2rem; }

        @media (max-width: 575.98px) {
            #scannerModal .scan-view, #scannerModal .scan-drop { min-height: 14rem; }
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
    // Super Admin (1) and Admin (2) have full access: on every pending requisition they get the
    // tools of the role its current step belongs to (Store Admin, Admin or Store Keeper).
    $hasFullAccess = in_array((int) Auth::user()->role_id, [1, 2], true);
    $isApprover = $hasFullAccess || (int) Auth::user()->role_id === 3;
    $canUseStoreAdminTools = $hasFullAccess || (int) Auth::user()->role_id === 3;
    // The QR box is for the Store Keeper's account only
    $canScan = (int) Auth::user()->role_id === 4;
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
                            {{-- The Super Admin sees the whole queue; everyone else what waits for them --}}
                            <p class="tw-mb-0 tw-mt-1 tw-text-sm tw-text-slate-500">{{ (int) Auth::user()->role_id === 1 ? __('requisition.pending_manage.subtitle_all') : __('requisition.pending_manage.subtitle') }}</p>
                        </div>

                        {{-- SCANNER TOOLS (Store Keeper - Role 4; Super Admin / Admin have full access) --}}
                        @if ($canScan)
                            {{-- One pill: QR mark, the field, and the camera button inside it --}}
                            <div class="tw-group tw-flex tw-w-full tw-items-center tw-gap-2 tw-rounded-2xl tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-p-1.5 tw-shadow-sm tw-transition focus-within:tw-border-brand-500 focus-within:tw-ring-4 focus-within:tw-ring-brand-500/15 hover:tw-border-slate-300 sm:tw-w-96 sm:tw-flex-none">
                                <label for="scan-qr-input" class="tw-sr-only">{{ __('requisition.pending_manage.scan_placeholder') }}</label>
                                <span class="tw-inline-flex tw-h-10 tw-w-10 tw-flex-none tw-items-center tw-justify-center tw-rounded-xl tw-bg-brand-50 tw-text-brand-600 tw-transition group-focus-within:tw-bg-brand-600 group-focus-within:tw-text-white" aria-hidden="true">
                                    <i class="fas fa-qrcode"></i>
                                </span>
                                <input type="text" id="scan-qr-input" autocomplete="off"
                                    class="tw-m-0 tw-h-10 tw-min-w-0 tw-flex-1 tw-border-0 tw-bg-transparent tw-p-0 tw-text-sm tw-font-medium tw-text-slate-900 tw-shadow-none tw-outline-none placeholder:tw-font-normal placeholder:tw-text-slate-400 focus:tw-outline-none focus:tw-ring-0"
                                    placeholder="{{ __('requisition.pending_manage.scan_placeholder') }}">
                                <button type="button" id="btn-open-camera"
                                    class="tw-inline-flex tw-h-10 tw-w-11 tw-flex-none tw-items-center tw-justify-center tw-rounded-xl tw-border-0 tw-bg-gradient-to-br tw-from-sky-500 tw-to-indigo-600 tw-text-white tw-shadow-md tw-shadow-indigo-600/30 tw-transition hover:tw-brightness-110 active:tw-scale-95 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/40"
                                    title="{{ __('requisition.pending_manage.scan_camera') }}" aria-label="{{ __('requisition.pending_manage.scan_camera') }}">
                                    <i class="fas fa-camera tw-text-xl" aria-hidden="true"></i>
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
                                            // Super Admin / Admin can decide on every requisition, whoever it waits for.
                                            $canAct = $hasFullAccess || ($isApprover && (int) $task->to_user_id === (int) Auth::id());
                                            // Role of the step the requisition is waiting at.
                                            $stepRole = (int) ($task->toUser->role_id ?? 0);
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
                                                <button type="button" class="view-action-btn tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-border-brand-200 tw-bg-brand-50 tw-text-sm tw-text-brand-700 tw-transition hover:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25"
                                                    data-id="{{ $req->id }}"
                                                    data-can-act="{{ $canAct ? 1 : 0 }}"
                                                    data-step-role="{{ $stepRole }}"
                                                    data-url="{{ route('requisitions.get_details', $req->id) }}"
                                                    data-tip="{{ $actionLabel }}"
                                                    aria-label="{{ $actionLabel }} #{{ $req->id }}">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
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

    {{-- Details / decision dialog (shared with the requisition list) --}}
    @include('requisition._decision_modal')

    {{-- SCANNER MODAL (Store Keeper; Super Admin / Admin have full access) --}}
    @if ($canScan)
        @php $scanT = __('requisition.pending_manage.modal'); @endphp
        <div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-labelledby="scannerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered scan-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header scan-header">
                        <span class="scan-header-icon" aria-hidden="true"><i class="fas fa-qrcode"></i></span>
                        <div class="modal-heading">
                            <h5 class="modal-title" id="scannerModalLabel">{{ $scanT['scan_title'] }}</h5>
                            <p class="modal-hint">{{ $scanT['scan_subtitle'] }}</p>
                        </div>
                        <x-ui.modal-today variant="bs" />
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('commontable.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body scan-body">
                        {{-- Camera or file --}}
                        <div class="scan-tabs" role="tablist" aria-label="{{ $scanT['scan_title'] }}">
                            <button type="button" class="scan-tab is-active" id="scan-tab-camera" role="tab" aria-selected="true" aria-controls="scan-panel-camera" data-panel="camera">
                                <i class="fas fa-camera" aria-hidden="true"></i> {{ $scanT['scan_tab_camera'] }}
                            </button>
                            <button type="button" class="scan-tab" id="scan-tab-file" role="tab" aria-selected="false" aria-controls="scan-panel-file" data-panel="file" tabindex="-1">
                                <i class="fas fa-file-upload" aria-hidden="true"></i> {{ $scanT['scan_tab_file'] }}
                            </button>
                        </div>

                        {{-- Camera (phone or PC) --}}
                        <div class="scan-panel" id="scan-panel-camera" role="tabpanel" aria-labelledby="scan-tab-camera">
                            <div class="scan-view" id="scan-view" data-state="idle">
                                {{-- The video is put in here by the QR library --}}
                                <div id="reader"></div>
                                <div class="scan-frame" aria-hidden="true"><span></span><span></span><span></span><span></span><i></i></div>
                                <div class="scan-idle">
                                    <span class="scan-idle-icon" aria-hidden="true"><i class="fas fa-camera"></i></span>
                                    <p class="scan-idle-text" id="scan-camera-message" role="status" aria-live="polite">{{ $scanT['scan_camera_idle'] }}</p>
                                    <button type="button" class="scan-btn scan-btn-primary" id="scan-camera-start">
                                        <i class="fas fa-play" aria-hidden="true"></i> {{ $scanT['scan_camera_start'] }}
                                    </button>
                                </div>
                                <p class="scan-live-hint">{{ $scanT['scan_camera_hint'] }}</p>
                            </div>
                            <button type="button" class="scan-btn scan-btn-quiet" id="scan-camera-stop" hidden>
                                <i class="fas fa-stop" aria-hidden="true"></i> {{ $scanT['scan_camera_stop'] }}
                            </button>
                        </div>

                        {{-- A file of the slip: a PDF or an image. A QR code found in it is handled
                             exactly like one seen by the camera. --}}
                        <div class="scan-panel" id="scan-panel-file" role="tabpanel" aria-labelledby="scan-tab-file" hidden>
                            <input type="file" id="scan-file-input" class="d-none" accept="application/pdf,image/png,image/jpeg,image/webp,image/gif,image/bmp">
                            <div class="scan-drop" id="scan-drop">
                                <span class="scan-drop-icon" aria-hidden="true"><i class="fas fa-cloud-upload-alt"></i></span>
                                <p class="scan-drop-title">{{ $scanT['scan_drop_title'] }}</p>
                                <p class="scan-drop-or">{{ $scanT['scan_drop_or'] }}</p>
                                <button type="button" class="scan-btn scan-btn-primary" id="scan-file-btn">
                                    <i class="fas fa-folder-open" aria-hidden="true"></i> {{ $scanT['scan_drop_browse'] }}
                                </button>
                                <p class="scan-drop-types">{{ $scanT['scan_drop_types'] }}</p>
                                <p class="scan-file-status" id="scan-file-status" role="status" aria-live="polite" hidden>
                                    <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> {{ $scanT['scan_reading_file'] }}
                                </p>
                            </div>
                        </div>

                        <p class="scan-note"><i class="fas fa-info-circle" aria-hidden="true"></i> {{ $scanT['scan_note'] }}</p>

                        {{-- Work area the QR library needs for decoding a file (never shown) --}}
                        <div id="scan-file-work" class="d-none"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('buttons.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@if (in_array((int) Auth::user()->role_id, [1, 2, 4], true))
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

        // A scan (camera, uploaded file or a number typed in the box) never takes stock out by
        // itself: the user is asked first.
        function processRequisitionViaScan(id) {
            Swal.fire({
                title: @json(__('requisition.pending_manage.modal.scan_confirm_title')).replace(':id', id),
                text: @json(__('requisition.pending_manage.modal.scan_confirm_text')),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: @json(__('requisition.pending_manage.modal.scan_confirm_yes')),
                cancelButtonText: @json(__('buttons.cancel')),
                focusCancel: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    takeStockOutForScan(id);
                }
            });
        }

        function takeStockOutForScan(id) {
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

        // ---- QR code from an uploaded file (PDF or image) ----
        @php
            $scanFileTexts = \Illuminate\Support\Arr::only(__('requisition.pending_manage.modal'), ['scan_file_no_qr', 'scan_file_type', 'scan_file_failed']);
        @endphp
        var scanText = @json($scanFileTexts);
        @php
            $scanCameraTexts = \Illuminate\Support\Arr::only(__('requisition.pending_manage.modal'), ['scan_camera_idle', 'scan_camera_starting', 'scan_camera_denied']);
        @endphp

        // pdf.js is only needed for PDF files, so it is fetched the first time one is chosen.
        var pdfJsLoading = null;
        function loadPdfJs() {
            if (window.pdfjsLib) { return Promise.resolve(window.pdfjsLib); }
            if (!pdfJsLoading) {
                pdfJsLoading = new Promise(function(resolve, reject) {
                    var script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
                    script.onload = function() {
                        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                        resolve(window.pdfjsLib);
                    };
                    script.onerror = function() { pdfJsLoading = null; reject(new Error('pdf.js could not be loaded')); };
                    document.head.appendChild(script);
                });
            }
            return pdfJsLoading;
        }

        // Reads the QR code of one image file; rejects when there is none.
        function decodeQrImage(file) {
            var reader = new Html5Qrcode('scan-file-work');
            return reader.scanFile(file, false).finally(function() {
                try { reader.clear(); } catch (error) { /* nothing to clear */ }
            });
        }

        // A PDF: each page (the first five at most) is drawn as an image and searched for a QR code.
        async function decodeQrPdf(file) {
            var pdfjs = await loadPdfJs();
            var pdf = await pdfjs.getDocument({ data: await file.arrayBuffer() }).promise;
            for (var number = 1; number <= Math.min(pdf.numPages, 5); number++) {
                var page = await pdf.getPage(number);
                var viewport = page.getViewport({ scale: 2.5 });
                var canvas = document.createElement('canvas');
                canvas.width = Math.ceil(viewport.width);
                canvas.height = Math.ceil(viewport.height);
                var context = canvas.getContext('2d');
                context.fillStyle = '#fff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                await page.render({ canvasContext: context, viewport: viewport }).promise;
                var blob = await new Promise(function(resolve) { canvas.toBlob(resolve, 'image/png'); });
                try {
                    return await decodeQrImage(new File([blob], 'page-' + number + '.png', { type: 'image/png' }));
                } catch (error) {
                    // no QR code on this page: try the next one
                }
            }
            throw new Error('no-qr');
        }

        async function scanUploadedFile(file) {
            if (!file) { return; }
            var isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
            var isImage = /^image\//.test(file.type);
            if (!isPdf && !isImage) {
                toastr.warning(scanText.scan_file_type);
                return;
            }
            if (typeof Html5Qrcode === 'undefined') {
                toastr.error(scanText.scan_file_failed);
                return;
            }

            $('#scan-file-status').prop('hidden', false);
            $('#scan-file-btn').prop('disabled', true);
            try {
                var text = isPdf ? await decodeQrPdf(file) : await decodeQrImage(file);
                // The same path as a camera scan: the requisition is verified and its stock is taken out.
                onScanSuccess(text, null);
            } catch (error) {
                var noQr = isImage || (error && error.message === 'no-qr');
                toastr.warning(noQr ? scanText.scan_file_no_qr : scanText.scan_file_failed);
            } finally {
                $('#scan-file-status').prop('hidden', true);
                $('#scan-file-btn').prop('disabled', false);
                $('#scan-file-input').val('');
            }
        }

        var scanCameraText = @json($scanCameraTexts);

        // Camera state of the dialog: idle | starting | live | error
        function setScanState(state, message) {
            $('#scan-view').attr('data-state', state);
            $('#scan-camera-stop').prop('hidden', state !== 'live');
            if (message) { $('#scan-camera-message').text(message); }
        }

        function startScanner() {
            $('#scannerModal').modal('show');
        }

        // Turns the camera on (the back camera on a phone) and reads QR codes from it.
        function startCamera() {
            if (html5QrcodeScanner) { return; }
            if (typeof Html5Qrcode === 'undefined') {
                setScanState('error', scanCameraText.scan_camera_denied);
                console.error("Scanner library not loaded.");
                return;
            }
            setScanState('starting', scanCameraText.scan_camera_starting);
            var camera = new Html5Qrcode('reader');
            html5QrcodeScanner = camera;
            camera.start({ facingMode: 'environment' }, {
                fps: 10,
                qrbox: function(width, height) {
                    var size = Math.floor(Math.min(width, height) * 0.7);
                    return { width: size, height: size };
                }
            }, onScanSuccess, onScanFailure).then(function() {
                // The dialog may have been closed while the camera was starting
                if (html5QrcodeScanner !== camera) {
                    camera.stop().then(function() { camera.clear(); }).catch(function() {});
                    return;
                }
                setScanState('live');
            }).catch(function() {
                if (html5QrcodeScanner === camera) { html5QrcodeScanner = null; }
                setScanState('error', scanCameraText.scan_camera_denied);
            });
        }

        function stopScanner() {
            var camera = html5QrcodeScanner;
            html5QrcodeScanner = null;
            setScanState('idle', scanCameraText.scan_camera_idle);
            if (!camera) { return; }
            var done = function() { try { camera.clear(); } catch (error) { /* nothing to clear */ } };
            try {
                camera.stop().then(done).catch(done);
            } catch (error) {
                done(); // the camera had not started yet
            }
        }

        // Camera | File
        function showScanPanel(name) {
            $('#scannerModal .scan-tab').each(function() {
                var active = $(this).data('panel') === name;
                $(this).toggleClass('is-active', active).attr('aria-selected', active ? 'true' : 'false').attr('tabindex', active ? 0 : -1);
            });
            $('#scan-panel-camera').prop('hidden', name !== 'camera');
            $('#scan-panel-file').prop('hidden', name !== 'file');
            if (name !== 'camera') { stopScanner(); }
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
                // Arriving from a notification (?highlight=169): that requisition's row gets a
                // background colour and the list opens on the page it is on.
                var highlight = (window.location.search.match(/[?&]highlight=(\d+)/) || [])[1];
                if (highlight) {
                    rows.forEach(function(row, index) {
                        if (String(row.getAttribute('data-id')) === highlight) {
                            row.classList.add('tw-bg-amber-100');
                            row.classList.remove('hover:tw-bg-slate-50/70');
                            state.page = Math.floor(index / state.perPage) + 1;
                        }
                    });
                }
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
            // Texts and the scanner flag for this page (the details / decision dialog has its own
            // script: resources/views/requisition/_decision_modal.blade.php).
            const T = @json(__('requisition.pending_manage.modal'));
            const canScan = {{ $canScan ? 'true' : 'false' }};

            // --- BIND SCANNER EVENTS (Store Keeper; Super Admin / Admin) ---
            if (canScan) {
                $('#btn-open-camera').click(function() {
                    startScanner();
                });
                // Closing the dialog always turns the camera off; it opens on the camera tab again
                $('#scannerModal').on('hidden.bs.modal', function() {
                    stopScanner();
                    showScanPanel('camera');
                });
                $('#scan-camera-start').on('click', startCamera);
                $('#scan-camera-stop').on('click', stopScanner);
                $('#scannerModal .scan-tab').on('click', function() {
                    showScanPanel($(this).data('panel'));
                });

                // A file may also be dropped on the upload area
                $('#scan-drop').on('dragenter dragover', function(e) {
                    e.preventDefault();
                    $(this).addClass('is-over');
                }).on('dragleave drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('is-over');
                }).on('drop', function(e) {
                    var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
                    scanUploadedFile(files && files[0]);
                });

                // Upload a PDF / image of the slip instead of using the camera
                $('#scan-file-btn').on('click', function() {
                    $('#scan-file-input').trigger('click');
                });
                $('#scan-file-input').on('change', function() {
                    scanUploadedFile(this.files && this.files[0]);
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

        });
    </script>
@endpush
