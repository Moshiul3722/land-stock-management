@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
@endpush

@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn ($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
    // Same building blocks as the stock list
    $card = 'tw-rounded-2xl tw-border tw-border-solid tw-border-slate-200/80 tw-bg-white tw-shadow-surface';
    $text = $isEn ? [
        'total' => 'Total in stock',
        'lots' => 'Lots',
        'back' => 'Back to stock list',
        'empty' => 'This product has no stock at the moment.',
        'total_row' => 'Total available stock',
    ] : [
        'total' => 'মোট মজুত',
        'lots' => 'লট',
        'back' => 'স্টক তালিকায় ফিরুন',
        'empty' => 'এই পণ্যটি এই মুহূর্তে স্টকে নেই।',
        'total_row' => 'মোট মজুত',
    ];
    $unitLabel = $product->product_unit && ! is_numeric($product->product_unit) ? $product->unit_label : '';
    $totalQuantity = $stockDetails->sum('quantity');
@endphp

@section('content')
    <section class="content-header list-page">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('stock.details_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('stock.index') }}" wire:navigate>{{ __('stock.list_title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.details') }}</li>
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

                {{-- ============ One row: totals and the way back (the product is in the page header) ============ --}}
                <section class="req-filters {{ $card }} tw-flex tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-3 tw-px-4 tw-py-3 sm:tw-px-5">
                    <div class="tw-flex tw-flex-1 tw-flex-wrap tw-items-center tw-gap-2">
                        <span class="tw-inline-flex tw-min-h-11 tw-items-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-bg-brand-50 tw-px-3 tw-text-sm tw-font-medium tw-text-brand-700">
                            <i class="fas fa-boxes" aria-hidden="true"></i>
                            {{ $text['total'] }}:
                            <span class="tw-font-bold tw-tabular-nums">{{ $digits($totalQuantity) }}</span>
                            @if ($unitLabel !== '') {{ $unitLabel }} @endif
                        </span>
                        <span class="tw-inline-flex tw-min-h-11 tw-items-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-bg-slate-100 tw-px-3 tw-text-sm tw-font-medium tw-text-slate-700">
                            <i class="fas fa-layer-group" aria-hidden="true"></i>
                            {{ $text['lots'] }}:
                            <span class="tw-font-bold tw-tabular-nums">{{ $digits($stockDetails->count()) }}</span>
                        </span>
                    </div>
                    <a href="{{ route('stock.index') }}" wire:navigate
                        class="tw-inline-flex tw-min-h-11 tw-w-full tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-text-sm tw-font-semibold tw-text-slate-700 tw-shadow-sm tw-transition hover:tw-bg-slate-50 hover:tw-text-slate-900 hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-slate-400/30 sm:tw-w-auto">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ $text['back'] }}
                    </a>
                </section>

                {{-- ============ Stock by lot ============ --}}
                <section class="{{ $card }} tw-relative tw-overflow-hidden">
                    @if ($stockDetails->count())
                        <div class="tw-overflow-x-auto">
                            <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-text-base tw-font-semibold tw-uppercase tw-tracking-wide tw-text-slate-600">
                                        <th scope="col" class="tw-w-12 tw-whitespace-nowrap tw-px-2 tw-py-2 tw-text-center sm:tw-w-20 sm:tw-px-3">{{ __('stock.table_sl') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 sm:tw-px-5">{{ __('lot.table_lot_number') }}</th>
                                        <th scope="col" class="tw-hidden tw-px-3 tw-py-2 sm:tw-table-cell sm:tw-px-5">{{ __('lot.purchase_type') }}</th>
                                        <th scope="col" class="tw-whitespace-nowrap tw-px-3 tw-py-2 tw-text-center sm:tw-px-5">{{ __('stock.table_quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stockDetails as $stockEntry)
                                        @php $purchaseType = $stockEntry->lot->purchaseType->name ?? '—'; @endphp
                                        <tr class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-transition hover:tw-bg-slate-50/70">
                                            <td class="tw-whitespace-nowrap tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600 sm:tw-px-3">{{ $digits($loop->iteration) }}</td>
                                            <td class="tw-px-3 tw-py-1.5 sm:tw-px-5">
                                                <span class="tw-block tw-font-semibold tw-leading-tight tw-text-slate-900 tw-wrap-anywhere">{{ $stockEntry->lot->lot_number ?? '—' }}</span>
                                                {{-- Phones: the purchase type is folded in here --}}
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500 sm:tw-hidden">{{ $purchaseType }}</span>
                                            </td>
                                            <td class="tw-hidden tw-px-3 tw-py-1.5 tw-text-slate-700 sm:tw-table-cell sm:tw-px-5">{{ $purchaseType }}</td>
                                            <td class="tw-whitespace-nowrap tw-px-3 tw-py-1.5 tw-text-center tw-tabular-nums sm:tw-px-5">
                                                <span class="tw-font-semibold tw-text-slate-900">{{ $digits($stockEntry->quantity) }}</span>
                                                @if ($unitLabel !== '')
                                                    <span class="tw-text-sm tw-text-slate-500">{{ $unitLabel }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="tw-bg-slate-50/80">
                                        <th scope="row" colspan="2" class="tw-px-3 tw-py-2.5 tw-text-right tw-font-semibold tw-text-slate-700 sm:tw-hidden">{{ $text['total_row'] }}</th>
                                        <th scope="row" colspan="3" class="tw-hidden tw-px-3 tw-py-2.5 tw-text-right tw-font-semibold tw-text-slate-700 sm:tw-table-cell sm:tw-px-5">{{ $text['total_row'] }}</th>
                                        <td class="tw-whitespace-nowrap tw-px-3 tw-py-2.5 tw-text-center tw-tabular-nums sm:tw-px-5">
                                            <span class="tw-font-bold tw-text-slate-900">{{ $digits($totalQuantity) }}</span>
                                            @if ($unitLabel !== '')
                                                <span class="tw-text-sm tw-text-slate-500">{{ $unitLabel }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="tw-flex tw-flex-col tw-items-center tw-px-6 tw-py-14 tw-text-center">
                            <div class="tw-mb-4 tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-brand-50 tw-text-xl tw-text-brand-600" aria-hidden="true">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <p class="tw-m-0 tw-text-base tw-font-semibold tw-text-slate-900">{{ $text['empty'] }}</p>
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>
@endsection
