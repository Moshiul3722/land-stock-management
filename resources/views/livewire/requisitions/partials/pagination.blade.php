{{-- Compact Livewire pagination (Tailwind, tw- prefix). Expects $paginator and $digits. --}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $window = collect(range(max(1, $current - 1), min($last, $current + 1)));
        $pages = $window->merge([1, $last])->unique()->sort()->values();
        $item = 'tw-inline-flex tw-h-10 tw-min-w-[2.5rem] tw-items-center tw-justify-center tw-rounded-lg tw-border tw-border-solid tw-px-2 tw-text-sm tw-font-medium tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-4 focus-visible:tw-ring-brand-500/25 disabled:tw-cursor-not-allowed disabled:tw-opacity-40';
        $idle = $item . ' tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50 hover:tw-text-slate-900';
        $active = $item . ' tw-border-brand-600 tw-bg-brand-600 tw-text-white';
    @endphp
    <nav role="navigation" aria-label="Pagination" class="tw-flex tw-items-center tw-gap-1">
        <button type="button" wire:click="previousPage" @disabled($paginator->onFirstPage()) class="{{ $idle }}" aria-label="{{ __('requisition.manage.previous_page') }}">
            <i class="fas fa-chevron-left tw-text-xs" aria-hidden="true"></i>
        </button>

        @foreach ($pages as $i => $page)
            @if ($i > 0 && $page - $pages[$i - 1] > 1)
                <span class="tw-px-1 tw-text-slate-400" aria-hidden="true">…</span>
            @endif
            @if ($page === $current)
                <span class="{{ $active }}" aria-current="page">{{ $digits($page) }}</span>
            @else
                <button type="button" wire:click="gotoPage({{ $page }})" class="{{ $idle }} {{ abs($page - $current) > 1 ? 'tw-hidden sm:tw-inline-flex' : '' }}">{{ $digits($page) }}</button>
            @endif
        @endforeach

        <button type="button" wire:click="nextPage" @disabled(! $paginator->hasMorePages()) class="{{ $idle }}" aria-label="{{ __('requisition.manage.next_page') }}">
            <i class="fas fa-chevron-right tw-text-xs" aria-hidden="true"></i>
        </button>
    </nav>
@endif
