{{--
    Today's weekday and date for a dialog header, placed just before the close button.

    Usage: <x-ui.modal-today />               Tailwind dialogs (inside .tw-root)
           <x-ui.modal-today variant="bs" />  Bootstrap dialogs (styled in public/css/app-modal.css)

    On phones it drops to its own line under the title.
--}}
@props(['variant' => 'tw'])

@php
    $isBn = app()->getLocale() === 'bn';
    $todayLabel = now()->locale($isBn ? 'bn' : 'en')->translatedFormat($isBn ? 'l, j F Y' : 'l, d M Y');
    if ($isBn) {
        $todayLabel = strtr($todayLabel, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
    $todayTitle = __('requisition.manage.today');
@endphp

@if ($variant === 'bs')
    <p class="modal-today" title="{{ $todayTitle }}">
        <i class="fas fa-calendar-day" aria-hidden="true"></i>
        <span class="sr-only">{{ $todayTitle }}:</span>
        <time datetime="{{ now()->format('Y-m-d') }}">{{ $todayLabel }}</time>
    </p>
@else
    <p class="tw-order-last tw-m-0 tw-flex tw-w-full tw-items-center tw-gap-2 tw-text-sm tw-font-medium tw-text-slate-600 sm:tw-order-none sm:tw-ml-auto sm:tw-w-auto sm:tw-self-center sm:tw-whitespace-nowrap"
        title="{{ $todayTitle }}">
        <i class="fas fa-calendar-day tw-text-brand-600" aria-hidden="true"></i>
        <span class="tw-sr-only">{{ $todayTitle }}:</span>
        <time datetime="{{ now()->format('Y-m-d') }}">{{ $todayLabel }}</time>
    </p>
@endif
