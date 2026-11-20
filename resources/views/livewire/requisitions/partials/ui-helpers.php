<?php

// Class names and small helpers shared by the requisition list page and the requisition
// details dialog opened from other pages (RequisitionViewer). Used as:
//   @php extract(require resource_path('views/livewire/requisitions/partials/ui-helpers.php')) @endphp

    $label = 'tw-mb-1.5 tw-block tw-text-[13px] tw-font-semibold tw-text-slate-600';
    $input = 'tw-block tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition placeholder:tw-text-slate-400 hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15 disabled:tw-cursor-not-allowed disabled:tw-bg-slate-100 disabled:tw-text-slate-400';
    $inputError = 'tw-border-rose-400 focus:tw-border-rose-500 focus:tw-ring-rose-500/15';
    $btn = 'tw-inline-flex tw-min-h-11 tw-select-none tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-px-4 tw-text-sm tw-font-semibold tw-leading-none tw-transition hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 active:tw-translate-y-px disabled:tw-cursor-not-allowed disabled:tw-opacity-60';
    $btnPrimary = $btn . ' tw-border-brand-600 tw-bg-brand-600 tw-text-white tw-shadow-sm hover:tw-border-brand-700 hover:tw-bg-brand-700 hover:tw-text-white focus-visible:tw-ring-brand-500/30';
    $btnSecondary = $btn . ' tw-border-slate-300 tw-bg-white tw-text-slate-700 tw-shadow-sm hover:tw-bg-slate-50 hover:tw-text-slate-900 focus-visible:tw-ring-slate-400/30';
    // Footer of the details dialog: close, print preview and print each have their own colour
    $btnClose = $btn . ' tw-border-stone-300 tw-bg-stone-100 tw-text-stone-700 tw-shadow-sm hover:tw-border-stone-400 hover:tw-bg-stone-200 hover:tw-text-stone-800 focus-visible:tw-ring-stone-400/30';
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

return compact('label', 'input', 'inputError', 'btn', 'btnPrimary', 'btnSecondary', 'btnClose', 'btnPreview', 'btnPrint', 'btnDanger', 'iconBtn', 'card', 'errorText', 'badge', 'dot');
