<?php

namespace App\Livewire\Concerns;

/**
 * Tailwind class strings shared by the Livewire CRUD modals, so every form looks like the
 * requisition modal. (This file is scanned by Tailwind — see tailwind.config.js.)
 */
final class Ui
{
    public const LABEL = 'tw-mb-1.5 tw-block tw-text-[13px] tw-font-semibold tw-text-slate-600';

    public const INPUT = 'tw-block tw-min-h-11 tw-w-full tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition placeholder:tw-text-slate-400 hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15 disabled:tw-cursor-not-allowed disabled:tw-bg-slate-100 disabled:tw-text-slate-400';

    public const INPUT_ERROR = 'tw-border-rose-400 focus:tw-border-rose-500 focus:tw-ring-rose-500/15';

    public const ERROR = 'tw-mt-1.5 tw-mb-0 tw-flex tw-items-start tw-gap-1.5 tw-text-[13px] tw-font-medium tw-text-rose-600';

    public const HINT = 'tw-mt-1.5 tw-mb-0 tw-text-xs tw-text-slate-500';

    private const BTN = 'tw-inline-flex tw-min-h-11 tw-select-none tw-items-center tw-justify-center tw-gap-2 tw-whitespace-nowrap tw-rounded-lg tw-border tw-border-solid tw-px-4 tw-text-sm tw-font-semibold tw-leading-none tw-transition hover:tw-no-underline focus:tw-outline-none focus-visible:tw-ring-4 active:tw-translate-y-px disabled:tw-cursor-not-allowed disabled:tw-opacity-60';

    public const BTN_PRIMARY = self::BTN . ' tw-border-brand-600 tw-bg-brand-600 tw-text-white tw-shadow-sm hover:tw-border-brand-700 hover:tw-bg-brand-700 hover:tw-text-white focus-visible:tw-ring-brand-500/30';

    public const BTN_SECONDARY = self::BTN . ' tw-border-slate-300 tw-bg-white tw-text-slate-700 tw-shadow-sm hover:tw-bg-slate-50 hover:tw-text-slate-900 focus-visible:tw-ring-slate-400/30';

    public const BTN_DANGER = self::BTN . ' tw-border-rose-600 tw-bg-rose-600 tw-text-white tw-shadow-sm hover:tw-border-rose-700 hover:tw-bg-rose-700 hover:tw-text-white focus-visible:tw-ring-rose-500/30';

    public const ICON_BTN = 'tw-inline-flex tw-h-11 tw-w-11 tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-border-transparent tw-bg-transparent tw-text-slate-500 tw-transition hover:tw-bg-slate-100 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 md:tw-h-9 md:tw-w-9';

    public const MODAL_HEADER = 'tw-flex tw-items-start tw-justify-between tw-gap-4 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-4 sm:tw-px-6';

    public const MODAL_TITLE = 'tw-m-0 tw-text-lg tw-font-bold tw-text-slate-900';

    public const MODAL_HINT = 'tw-mb-0 tw-mt-0.5 tw-text-sm tw-text-slate-500';

    public const MODAL_BODY = 'tw-flex-1 tw-space-y-4 tw-overflow-y-auto tw-overscroll-contain tw-px-5 tw-py-5 sm:tw-px-6';

    public const MODAL_FOOTER = 'tw-flex tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-3 sm:tw-flex-row sm:tw-justify-end sm:tw-px-6';

    public const FORM_ALERT = 'tw-flex tw-items-start tw-gap-3 tw-rounded-xl tw-border tw-border-solid tw-border-rose-200 tw-bg-rose-50 tw-px-4 tw-py-3 tw-text-sm tw-text-rose-800';
}
