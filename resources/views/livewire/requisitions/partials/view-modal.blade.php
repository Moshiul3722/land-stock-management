{{-- The requisition details dialog: used on the requisition list and, through
     RequisitionViewer, on other pages (e.g. a product's issue history). --}}
        <x-ui.modal wire:model="showView" max-width="3xl" labelledby="req-view-title">
            @if ($viewed = $this->viewing)
                <div class="tw-flex tw-flex-wrap tw-items-start tw-justify-between tw-gap-x-4 tw-gap-y-1 tw-border-0 tw-border-b tw-border-solid tw-border-slate-200 tw-px-5 tw-py-3 sm:tw-flex-nowrap sm:tw-px-6">
                    <div class="tw-flex tw-min-w-0 tw-flex-1 tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1">
                        <h2 id="req-view-title" class="tw-m-0 tw-text-[20px] tw-font-bold tw-text-slate-900">{{ __('requisition.manage.view_title', ['id' => $digits($viewed->id)]) }}</h2>
                        <span class="{{ $badge($viewed->status) }}"><span class="{{ $dot($viewed->status) }}" aria-hidden="true"></span>{{ $statusLabel($viewed->status) }}</span>
                    </div>
                    {{-- Today's weekday and date; on phones it wraps under the title --}}
                    <p class="tw-order-last tw-m-0 tw-flex tw-w-full tw-items-center tw-gap-2 tw-text-sm tw-font-medium tw-text-slate-600 sm:tw-order-none sm:tw-ml-auto sm:tw-w-auto sm:tw-self-center sm:tw-whitespace-nowrap"
                        title="{{ __('requisition.manage.today') }}">
                        <i class="fas fa-calendar-day tw-text-brand-600" aria-hidden="true"></i>
                        <span class="tw-sr-only">{{ __('requisition.manage.today') }}:</span>
                        <time datetime="{{ now()->format('Y-m-d') }}">{{ $todayLabel }}</time>
                    </p>
                    <button type="button" x-on:click="open = false" class="{{ $iconBtn }} tw--mr-2 tw--mt-1 !tw-bg-slate-100 !tw-text-slate-600 hover:!tw-bg-slate-200 hover:!tw-text-slate-900" aria-label="{{ __('requisition.manage.close') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="tw-flex-1 tw-space-y-4 tw-overflow-y-auto tw-overscroll-contain tw-px-5 tw-py-4 sm:tw-px-6">
                    <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-3">
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_on') }}</dt>
                            <dd class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900">{{ $formatDate($viewed->requisition_date) }}</dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.requested_by') }}</dt>
                            <dd class="tw-m-0 tw-truncate tw-font-semibold tw-leading-snug tw-text-slate-900">{{ $viewed->requestedBy->name ?? '—' }}</dd>
                        </div>
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-2.5">
                            <dt class="tw-text-xs tw-font-medium tw-leading-tight tw-text-slate-500">{{ __('requisition.manage.items') }}</dt>
                            <dd class="tw-m-0 tw-font-semibold tw-leading-snug tw-text-slate-900">{{ __('requisition.manage.items_summary', ['count' => $digits($viewed->items->count())]) }}</dd>
                        </div>
                    </dl>

                    <div>
                        <h3 class="tw-mb-2 tw-mt-0 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">{{ __('requisition.manage.item_list') }}</h3>
                        @php
                            // Five items per page; the page is clamped in case the list got shorter.
                            $viewTotal = $viewed->items->count();
                            $viewPages = max(1, (int) ceil($viewTotal / $viewItemsPerPage));
                            $viewPage = min(max(1, $viewItemPage), $viewPages);
                            $viewItems = $viewed->items->forPage($viewPage, $viewItemsPerPage);
                        @endphp
                        <div class="tw-overflow-x-auto tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                            <table class="tw-m-0 tw-w-full tw-border-collapse tw-text-left tw-text-base">
                                <thead>
                                    <tr class="tw-bg-slate-50 tw-text-base tw-font-semibold tw-text-slate-600">
                                        {{-- Same columns and names as the dialog of the requisition request list --}}
                                        <th scope="col" class="tw-w-12 tw-px-2 tw-py-2 tw-text-center sm:tw-w-20 sm:tw-whitespace-nowrap">{{ __('lot.table_sl') }}</th>
                                        <th scope="col" class="tw-px-4 tw-py-2">{{ __('requisition.manage.product') }}</th>
                                        <th scope="col" class="tw-px-3 tw-py-2 tw-text-right sm:tw-whitespace-nowrap">{{ __('requisition.requested_quantity') }}</th>
                                        <th scope="col" class="tw-px-4 tw-py-2 tw-text-center sm:tw-whitespace-nowrap">{{ __('requisition.approved_quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewItems as $item)
                                        @php
                                            $viewUnit = $item->product && $item->product->product_unit && ! is_numeric($item->product->product_unit) ? $item->product->unit_label : '';
                                        @endphp
                                        <tr wire:key="view-item-{{ $item->id }}" class="tw-border-0 tw-border-t tw-border-solid tw-border-slate-100">
                                            <td class="tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600">{{ $digits(($viewPage - 1) * $viewItemsPerPage + $loop->iteration) }}</td>
                                            <td class="tw-px-4 tw-py-1.5">
                                                <span class="tw-block tw-break-words tw-font-medium tw-leading-tight tw-text-slate-900">{{ $item->product->name ?? '—' }}</span>
                                                <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500">{{ collect([$item->product->category->name ?? null, $item->product->subCategory->name ?? null])->filter()->join(' · ') }}</span>
                                            </td>
                                            <td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-right tw-tabular-nums">
                                                <span class="tw-font-semibold tw-text-slate-900">{{ $digits($item->original_quantity ?? $item->quantity) }}</span>
                                                @if ($viewUnit !== '')
                                                    <span class="tw-text-sm tw-text-slate-500">{{ $viewUnit }}</span>
                                                @endif
                                            </td>
                                            {{-- Approved quantity: shown as soon as an approver has approved the requisition
                                                 (the same figure as in the dialog of the requisition request list); a dash
                                                 while it is a draft or still waits for its first approval. --}}
                                            <td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-center tw-tabular-nums">
                                                @if ($viewed->status === 'draft' || ($viewed->status === 'pending' && ! $viewed->approvalDetails->contains('status', 'approved')))
                                                    <span class="tw-font-semibold tw-text-slate-900">—</span>
                                                @else
                                                    <span class="tw-font-semibold tw-text-slate-900">{{ $digits($item->quantity) }}</span>
                                                    @if ($viewUnit !== '')
                                                        <span class="tw-text-sm tw-text-slate-500">{{ $viewUnit }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- More than five items: the rest are on further pages --}}
                        @if ($viewPages > 1)
                            <nav class="tw-mt-2 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2" aria-label="{{ __('commontable.pagination') }}">
                                <p class="tw-m-0 tw-text-xs tw-font-medium tw-text-slate-600" aria-live="polite">
                                    {{ strtr(__('commontable.dt_info'), ['_TOTAL_' => $digits($viewTotal), '_START_' => $digits(($viewPage - 1) * $viewItemsPerPage + 1), '_END_' => $digits(min($viewPage * $viewItemsPerPage, $viewTotal))]) }}
                                </p>
                                <div class="tw-flex tw-flex-wrap tw-gap-1">
                                    <button type="button" wire:click="gotoViewItemPage({{ $viewPage - 1 }})" @disabled($viewPage === 1)
                                        aria-label="{{ __('commontable.dt_previous') }}" title="{{ __('commontable.dt_previous') }}"
                                        class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-px-2 tw-text-slate-600 tw-transition hover:tw-bg-slate-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40">
                                        <i class="fas fa-chevron-left tw-text-xs" aria-hidden="true"></i>
                                    </button>
                                    @for ($page = 1; $page <= $viewPages; $page++)
                                        <button type="button" wire:click="gotoViewItemPage({{ $page }})" wire:key="req-view-page-{{ $page }}"
                                            @if ($page === $viewPage) aria-current="page" @endif
                                            class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-px-2 tw-text-xs tw-font-semibold tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 {{ $page === $viewPage ? 'tw-border-brand-600 tw-bg-brand-600 tw-text-white' : 'tw-border-slate-200 tw-bg-white tw-text-slate-600 hover:tw-bg-slate-50' }}">
                                            {{ $digits($page) }}
                                        </button>
                                    @endfor
                                    <button type="button" wire:click="gotoViewItemPage({{ $viewPage + 1 }})" @disabled($viewPage === $viewPages)
                                        aria-label="{{ __('commontable.dt_next') }}" title="{{ __('commontable.dt_next') }}"
                                        class="tw-inline-flex tw-h-8 tw-min-w-[2rem] tw-items-center tw-justify-center tw-rounded-md tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-px-2 tw-text-slate-600 tw-transition hover:tw-bg-slate-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40">
                                        <i class="fas fa-chevron-right tw-text-xs" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </nav>
                        @endif
                    </div>

                    <div>
                        <h3 class="tw-mb-2 tw-mt-0 tw-flex tw-items-center tw-gap-2 tw-text-sm tw-font-semibold tw-leading-snug tw-text-slate-700">
                            <i class="fas fa-history tw-text-slate-400" aria-hidden="true"></i>{{ __('requisition.manage.timeline') }}
                        </h3>
                        {{-- A compact timeline in one box: one line per step with its status icon, number,
                             what happened and who did it (or is awaited); the date sits on the right (under it
                             on phones). A comment, if any, follows in a small line. --}}
                        <ol class="tw-m-0 tw-list-none tw-overflow-hidden tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-p-0">
                            @foreach ($this->timeline as $step)
                                @php
                                    [$stepIcon, $stepDot, $stepRow] = match ($step['state']) {
                                        'approved', 'processed' => ['fa-check', 'tw-bg-emerald-500 tw-text-white', ''],
                                        'rejected' => ['fa-times', 'tw-bg-rose-500 tw-text-white', 'tw-bg-rose-50/70'],
                                        'pending' => ['fa-hourglass-half', 'tw-bg-amber-400 tw-text-white', 'tw-bg-amber-50/70'],
                                        default => ['fa-file-alt', 'tw-bg-brand-600 tw-text-white', ''],
                                    };
                                    $stepTitle = $step['state'] === 'initiated' ? __('requisition.manage.step_initiated') : $statusLabel($step['state']);
                                    $stepWho = $step['state'] === 'pending'
                                        ? __('requisition.manage.waiting_for', ['name' => $step['name'] ?: '—'])
                                        : ($step['name'] ? __('requisition.manage.by', ['name' => $step['name']]) : '');
                                @endphp
                                <li class="tw-relative tw-flex tw-gap-3 tw-px-3 tw-py-2 {{ $stepRow }} {{ $loop->last ? '' : 'tw-border-0 tw-border-b tw-border-solid tw-border-slate-100' }}">
                                    {{-- The line joining the step icons --}}
                                    @unless ($loop->last)
                                        <span class="tw-absolute tw-bottom-0 tw-left-[1.4375rem] tw-top-9 tw-w-px tw-bg-slate-200" aria-hidden="true"></span>
                                    @endunless
                                    @unless ($loop->first)
                                        <span class="tw-absolute tw-left-[1.4375rem] tw-top-0 tw-h-2 tw-w-px tw-bg-slate-200" aria-hidden="true"></span>
                                    @endunless
                                    <span class="tw-relative tw-mt-0.5 tw-inline-flex tw-h-6 tw-w-6 tw-flex-none tw-items-center tw-justify-center tw-rounded-full tw-text-[11px] tw-shadow-sm {{ $stepDot }}">
                                        <i class="fas {{ $stepIcon }}" aria-hidden="true"></i>
                                    </span>
                                    <div class="tw-min-w-0 tw-flex-1">
                                        <div class="tw-flex tw-flex-wrap tw-items-baseline tw-justify-between tw-gap-x-3 tw-gap-y-0.5">
                                            <p class="tw-m-0 tw-min-w-0 tw-text-sm tw-leading-snug">
                                                <span class="tw-mr-1 tw-text-xs tw-font-bold tw-tabular-nums tw-text-slate-400">{{ $digits($loop->iteration) }}.</span>
                                                <span class="tw-font-semibold tw-text-slate-900">{{ $stepTitle }}</span>
                                                @if ($stepWho !== '')
                                                    <span class="tw-text-slate-300" aria-hidden="true">·</span>
                                                    <span class="tw-break-words tw-text-[13px] tw-text-slate-600">{{ $stepWho }}</span>
                                                @endif
                                            </p>
                                            <span class="tw-inline-flex tw-flex-none tw-items-center tw-gap-1 tw-whitespace-nowrap tw-text-xs tw-text-slate-500">
                                                <i class="fas fa-clock tw-text-slate-400" aria-hidden="true"></i>{{ $formatDate($step['date'], true) }}
                                            </span>
                                        </div>
                                        @if ($step['comments'])
                                            <p class="tw-mb-0 tw-mt-1 tw-flex tw-gap-1.5 tw-rounded-md tw-bg-slate-50 tw-px-2 tw-py-1 tw-text-[13px] tw-italic tw-leading-snug tw-text-slate-600">
                                                <i class="fas fa-quote-left tw-mt-0.5 tw-text-[10px] tw-text-slate-400" aria-hidden="true"></i>
                                                <span class="tw-min-w-0 tw-break-words">{{ $step['comments'] }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                        @if (count($this->timeline) === 1 && $viewed->status === 'draft')
                            <p class="tw-mb-0 tw-mt-3 tw-text-xs tw-text-slate-500">{{ __('requisition.manage.timeline_empty') }}</p>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-foot tw-flex tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-2.5 sm:tw-flex-row sm:tw-justify-end sm:tw-px-6">
                    @php
                        // The slip is for collecting the goods. While the requisition is still pending the
                        // buttons stay; they go once it is approved and verified by QR scan.
                        $canPrintSlip = $viewed->is_print_enabled
                            && ($viewed->status === 'pending' || ! $viewed->is_physically_verified);
                    @endphp
                    <button type="button" x-on:click="open = false" class="{{ $btnClose }}">{{ __('requisition.manage.close') }}</button>
                    @if ($canPrintSlip)
                        {{-- The slip on an A4 sheet in a new tab, without printing straight away --}}
                        <a href="{{ route('requisitions.print', ['requisition' => $viewed, 'preview' => 1]) }}" target="_blank" rel="noopener" class="{{ $btnPreview }}">
                            <i class="fas fa-eye" aria-hidden="true"></i> {{ __('requisition.manage.print_preview') }}
                        </a>
                    @endif
                    @if ($canPrintSlip)
                        <a href="{{ route('requisitions.print', $viewed) }}" target="_blank" rel="noopener" class="{{ $btnPrint }}">
                            <i class="fas fa-print" aria-hidden="true"></i> {{ __('requisition.manage.print') }}
                        </a>
                    @endif
                    @if ($canEdit($viewed))
                        <button type="button" wire:click="confirmDelete({{ $viewed->id }})" class="{{ $btnSecondary }} tw-text-rose-600 hover:tw-text-rose-700">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i> {{ __('requisition.manage.delete') }}
                        </button>
                        <button type="button" wire:click="openEdit({{ $viewed->id }})" class="{{ $btnSecondary }}">
                            <i class="fas fa-pen" aria-hidden="true"></i> {{ __('requisition.manage.edit') }}
                        </button>
                        <button type="button" wire:click="submitDraft" wire:loading.attr="disabled" wire:target="submitDraft" class="{{ $btnPrimary }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="submitDraft"></i>
                            <i class="fas fa-paper-plane" aria-hidden="true" wire:loading.remove wire:target="submitDraft"></i>
                            {{ __('requisition.manage.submit') }}
                        </button>
                    @endif
                </div>
            @endif
        </x-ui.modal>
