{{--
    Requisition details / decision dialog, with its script.

    Used by the requisition request list (requisition/pending.blade.php) and by the requisition
    list (crud/host.blade.php, for requisitions the signed-in user can decide on). A page opens
    it with a button of the class `view-action-btn` (see the script below for its data attributes).
    Needs Tailwind's stylesheet on the page; Bootstrap opens and closes the dialog.
--}}
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
    $canScan = $hasFullAccess || (int) Auth::user()->role_id === 4;
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

                            {{-- BUTTONS: STORE ADMIN (3); Super Admin / Admin at the Store Admin step --}}
                            @if ($canUseStoreAdminTools)
                                <div id="store-admin-tools" class="tw-flex tw-flex-wrap tw-gap-2">
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
                                            {{-- Checkbox: Store Admin (3); Super Admin / Admin at the Store Admin step --}}
                                            @if ($canUseStoreAdminTools)
                                                <th scope="col" id="select-all-th" class="tw-w-10 tw-px-2 tw-py-2 tw-text-center">
                                                    <input type="checkbox" id="select-all-items" class="tw-h-5 tw-w-5 tw-cursor-pointer tw-align-middle tw-accent-brand-600"
                                                        title="{{ __('requisition.pending_manage.modal.select_all') }}" aria-label="{{ __('requisition.pending_manage.modal.select_all') }}">
                                                </th>
                                            @endif
                                            <th scope="col" class="tw-px-4 tw-py-2">{{ __('requisition.manage.product') }}</th>
                                            {{-- When the requester last received this product, and how many --}}
                                            <th scope="col" class="tw-hidden tw-px-3 tw-py-2 tw-text-center sm:tw-table-cell">{{ __('requisition.manage.previous_requisition') }}</th>
                                            <th scope="col" class="tw-px-3 tw-py-2 tw-text-center sm:tw-whitespace-nowrap">{{ __('requisition.requested_quantity') }}</th>
                                            <th scope="col" class="tw-px-4 tw-py-2 tw-text-center sm:tw-whitespace-nowrap">{{ __('requisition.approved_quantity') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-items-body"></tbody>
                                </table>
                            </div>

                            {{-- More than five items: the rest are on further pages (the rows stay in the form) --}}
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
                <div class="ui-modal-foot tw-flex tw-flex-none tw-flex-col-reverse tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-200 tw-bg-slate-50/80 tw-px-5 tw-py-2.5 sm:tw-flex-row sm:tw-justify-end sm:tw-px-6">
                    {{-- Print preview and print: for every user, once the Admin has approved the
                         requisition (that is when printing is switched on) --}}
                    <div id="print-buttons" class="tw-flex tw-flex-col-reverse tw-gap-2 sm:tw-mr-auto sm:tw-flex-row" style="display: none;">
                        <a href="#" id="btn-print-preview" target="_blank" rel="noopener" class="{{ $btnSecondary }}">
                            <i class="fas fa-eye" aria-hidden="true"></i> {{ __('requisition.manage.print_preview') }}
                        </a>
                        <a href="#" id="btn-print" target="_blank" rel="noopener" class="{{ $btnSecondary }}">
                            <i class="fas fa-print" aria-hidden="true"></i> {{ __('requisition.manage.print') }}
                        </a>
                    </div>

                    <button type="button" data-dismiss="modal" class="{{ $btnSecondary }}">{{ __('commontable.close') }}</button>

                    {{-- Actions: Visible for Super Admin (1), Admin (2) & Store Admin (3) --}}
                    @if ($isApprover)
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

    {{-- Hidden Forms --}}
    <form id="approve-form" method="POST" style="display:none;">@csrf</form>
    <form id="reject-form" method="POST" style="display:none;">@csrf</form>

@push('js')
    <script>
        $(document).ready(function() {
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
            const previousLabel = @json(__('requisition.manage.previous_requisition'));
            const previousQuantityText = @json(__('requisition.manage.previous_quantity'));
            const useBanglaDigits = @json(app()->getLocale() !== 'en');
            const dateLocale = useBanglaDigits ? 'bn-BD' : 'en-GB';

            function localDigits(value) {
                const text = String(value);
                return useBanglaDigits ? text.replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d]) : text;
            }

            // "In stock (:qty)": the number is how many are in stock now
            const inStockLabel = @json(__('requisition.manage.in_stock'));
            const stepUpLabel = @json(__('commontable.increase'));
            const stepDownLabel = @json(__('commontable.decrease'));

            // Bangla digits back to 0-9 (what the server expects).
            function asciiDigits(value) {
                return String(value == null ? '' : value).replace(/[০-৯]/g, d => '০১২৩৪৫৬৭৮৯'.indexOf(d));
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
            // Super Admin / Admin have full access: the dialog is the same for them at every step
            // (isStoreAdmin is switched on when they open a requisition).
            const hasFullAccess = {{ $hasFullAccess ? 'true' : 'false' }};
            const canUseStoreAdminTools = {{ $canUseStoreAdminTools ? 'true' : 'false' }};
            let isStoreAdmin = {{ Auth::user()->role_id == 3 ? 'true' : 'false' }};
            const isStoreKeeper = {{ Auth::user()->role_id == 4 ? 'true' : 'false' }};
            const canScan = {{ $canScan ? 'true' : 'false' }};
            const isApprover = {{ $isApprover ? 'true' : 'false' }};

            // ---- Items of the dialog: five per page ----
            // Every row stays in the table (only hidden), so saving, splitting and deleting
            // still see the quantities and ticks of all pages.
            const ITEMS_PER_PAGE = 5;
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
            // Any button with the class `view-action-btn` opens the dialog. It carries:
            //   data-id         requisition id
            //   data-url        where the details are fetched from
            //   data-can-act    1 when the signed-in user may decide on it
            //   data-step-role  role of the approval step it is waiting at
            $(document).on('click', '.view-action-btn', function() {
                let url = $(this).data('url');
                currentRequisitionId = $(this).data('id');

                $('#modal-req-no').text('#' + localDigits(currentRequisitionId));
                $('#actionModal').modal('show');
                $('#btn-split-notify, #btn-delete-items').hide();
                $('#select-all-items').prop('checked', false).prop('indeterminate', false);

                if (hasFullAccess) {
                    // Super Admin / Admin get the full dialog at every step: tick boxes, stock
                    // labels, editable approved quantities and the save button.
                    isStoreAdmin = true;
                    $('#store-admin-tools, #select-all-th').show();
                    $('#btn-approve').prop('disabled', false).removeAttr('title');
                }

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
                    '<tr><td colspan="6" class="tw-px-4 tw-py-6 tw-text-center tw-text-sm tw-text-slate-500">' + esc(T.loading_items) + '</td></tr>');
                $('#modal-requestor, #modal-date, #modal-items-count').text('…');
                $('#modal-items-pager').addClass('tw-hidden').removeClass('tw-flex');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(showDate(res.date_iso, res.formatted_date, false));
                        totalItemsInModal = res.requisition.items.length;
                        // Print preview / print: shown to everyone once printing is switched on
                        // (after the Admin's approval)
                        {
                            let printUrl = "{{ route('requisitions.print', ':id') }}".replace(
                                ':id', currentRequisitionId);
                            $('#btn-print').attr('href', printUrl);
                            $('#btn-print-preview').attr('href', printUrl + '?preview=1');
                            // (flex, not jQuery's show(): the two buttons sit in a flex row)
                            $('#print-buttons').css('display', res.is_print_enabled ? 'flex' : 'none');
                        }
                        $('#modal-items-count').text(T.items_count.replace(':count', localDigits(totalItemsInModal)));

                        // ---- Item rows (same row style as the requisition view dialog) ----
                        const cell = 'tw-px-3 tw-py-1.5';
                        const fieldBase = 'tw-min-h-9 tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-text-sm tw-text-slate-900 tw-shadow-sm tw-transition hover:tw-border-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-brand-500/15';
                        let itemsHtml = '';
                        res.requisition.items.forEach((item, itemIndex) => {
                            // Product Unit
                            let unit = item.product.unit_label || item.product.product_unit || '';
                            // Lots are assigned automatically on approval; the Store Admin only needs
                            // to know when a product is out of stock (those items can be split off).
                            const available = item.available_quantity != null ? Number(item.available_quantity) : null;
                            const isUnavailable = isStoreAdmin && available !== null && available <= 0;
                            const isShort = isStoreAdmin && ! isUnavailable && available !== null && available < Number(item.quantity);
                            let stockNote = '';
                            if (isUnavailable) {
                                stockNote = `<span class="stock-unavailable tw-inline-flex tw-flex-none tw-items-center tw-gap-1 tw-whitespace-nowrap tw-rounded-full tw-px-2 tw-py-0.5 tw-text-xs tw-font-semibold tw-ring-1 tw-ring-inset tw-bg-rose-50 tw-text-rose-700 tw-ring-rose-200"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-rose-500" aria-hidden="true"></span>${esc(T.stock_unavailable)}</span>`;
                            } else if (isShort) {
                                stockNote = `<span class="tw-inline-flex tw-flex-none tw-items-center tw-gap-1 tw-whitespace-nowrap tw-rounded-full tw-px-2 tw-py-0.5 tw-text-xs tw-font-semibold tw-ring-1 tw-ring-inset tw-bg-amber-50 tw-text-amber-800 tw-ring-amber-300"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-amber-500" aria-hidden="true"></span>${esc(T.stock_short.replace(':qty', localDigits(available)))}</span>`;
                            } else if (isStoreAdmin && available !== null) {
                                stockNote = `<span class="tw-inline-flex tw-flex-none tw-items-center tw-gap-1 tw-whitespace-nowrap tw-rounded-full tw-px-2 tw-py-0.5 tw-text-xs tw-font-semibold tw-ring-1 tw-ring-inset tw-bg-emerald-50 tw-text-emerald-700 tw-ring-emerald-200"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>${esc(inStockLabel.replace(':qty', localDigits(available)))}</span>`;
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
                                    `<td class="tw-px-2 tw-py-1.5 tw-text-center"><input type="checkbox" class="split-item-checkbox tw-h-5 tw-w-5 tw-cursor-pointer tw-align-middle tw-accent-brand-600" value="${item.id}" data-unavailable="${isUnavailable ? 1 : 0}" aria-label="${esc(T.select_item)}: ${esc(item.product.name)}"></td>`;
                            }

                            // Approved quantity: a field for the Store Admin, plain text for everyone else.
                            // The control has one fixed width (the unit label takes what is left), so it is equally wide in every row whatever the unit is.
                            // The unit follows the number in both cases.
                            const unitHtml = unit ? `<span class="tw-text-sm tw-text-slate-500">${esc(unit)}</span>` : '';
                            let approvedHtml = isStoreAdmin ?
                                `<span class="tw-inline-flex tw-min-w-32 tw-items-stretch tw-overflow-hidden tw-rounded-lg tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-align-middle tw-shadow-sm tw-transition hover:tw-border-slate-400 focus-within:tw-border-brand-500 focus-within:tw-ring-2 focus-within:tw-ring-brand-500/20">
                                    <input type="text" inputmode="numeric" autocomplete="off" maxlength="5" class="item-qty-input tw-block tw-min-h-8 tw-w-10 tw-flex-none tw-border-0 tw-bg-transparent tw-px-0.5 tw-py-0 tw-text-center tw-text-base tw-font-bold tw-tabular-nums tw-text-slate-900 focus:tw-outline-none" data-id="${item.id}" value="${localDigits(item.quantity)}" aria-label="${esc(T.approved_quantity)}: ${esc(item.product.name)}">
                                    <span class="tw-flex tw-w-6 tw-flex-none tw-flex-col tw-border-0 tw-border-l tw-border-solid tw-border-slate-200 sm:tw-w-5">
                                        <button type="button" class="item-qty-step tw-flex tw-flex-1 tw-items-center tw-justify-center tw-border-0 tw-bg-transparent tw-p-0 tw-text-slate-400 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 active:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-bg-brand-50 focus-visible:tw-text-brand-700" data-step="1" tabindex="-1" aria-label="${esc(stepUpLabel)}" title="${esc(stepUpLabel)}"><i class="fas fa-caret-up tw-text-xs" aria-hidden="true"></i></button>
                                        <button type="button" class="item-qty-step tw-flex tw-flex-1 tw-items-center tw-justify-center tw-border-0 tw-bg-transparent tw-p-0 tw-text-slate-400 tw-transition hover:tw-bg-brand-50 hover:tw-text-brand-700 active:tw-bg-brand-100 focus:tw-outline-none focus-visible:tw-bg-brand-50 focus-visible:tw-text-brand-700 tw-border-t tw-border-solid tw-border-slate-200" data-step="-1" tabindex="-1" aria-label="${esc(stepDownLabel)}" title="${esc(stepDownLabel)}"><i class="fas fa-caret-down tw-text-xs" aria-hidden="true"></i></button>
                                    </span>
                                    ${unit ? `<span class="item-qty-unit tw-flex tw-flex-1 tw-items-center tw-justify-center tw-border-0 tw-border-l tw-border-solid tw-border-brand-100 tw-bg-brand-50 tw-px-2.5 tw-text-sm tw-font-semibold tw-text-brand-700 sm:tw-text-[15px]">${esc(unit)}</span>` : ''}
                                </span>` :
                                `<span class="tw-font-semibold tw-tabular-nums tw-text-slate-900">${localDigits(item.quantity)}</span> ${unitHtml}`;

                            // Previous requisition: the date the requester last received this product, with
                            // the quantity received in brackets. A dash when it was never received before.
                            const previous = item.previous_receipt;
                            const previousDate = previous ? showDate(previous.date_iso, previous.date_iso, false) : '';
                            const previousQty = previous ? previousQuantityText.replace(':qty', localDigits(previous.quantity)) : '';
                            const previousHtml = previous ?
                                `<span class="tw-block tw-whitespace-nowrap">${esc(previousDate)}</span><span class="tw-block tw-whitespace-nowrap tw-text-sm tw-font-medium tw-text-slate-500">(${esc(previousQty)})</span>` :
                                '—';
                            // Phones: the column is folded in under the product
                            const previousPhone = previous ?
                                `<span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500 sm:tw-hidden">${esc(previousLabel)}: ${esc(previousDate)} (${esc(previousQty)})</span>` :
                                '';

                            itemsHtml += `
                            <tr class="modal-item-row tw-border-0 tw-border-t tw-border-solid tw-border-slate-100">
                                <td class="tw-px-2 tw-py-1.5 tw-text-center tw-tabular-nums tw-text-slate-600">${localDigits(itemIndex + 1)}</td>
                                ${checkboxHtml}
                                <td class="tw-px-4 tw-py-1.5">
                                    <span class="tw-flex tw-flex-wrap tw-items-center tw-gap-x-2 tw-gap-y-0.5">
                                        <span class="tw-min-w-0 tw-break-words tw-font-medium tw-leading-tight tw-text-slate-900">${esc(item.product.name)}</span>
                                        ${stockNote}
                                    </span>
                                    <span class="tw-block tw-text-sm tw-leading-tight tw-text-slate-500">${categoryDisplay}</span>
                                    ${previousPhone}
                                </td>
                                <td class="tw-hidden ${cell} tw-text-center tw-leading-tight tw-text-slate-700 sm:tw-table-cell">${previousHtml}</td>
                                <td class="tw-whitespace-nowrap ${cell} tw-text-center tw-tabular-nums">
                                    <span class="tw-font-semibold tw-text-slate-900">${localDigits(item.original_quantity || item.quantity)}</span>
                                    ${unitHtml}
                                </td>
                                <td class="tw-whitespace-nowrap tw-px-4 tw-py-1.5 tw-text-center">${approvedHtml}</td>
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
                            // A comment left with a step: blue text on a light blue note, set apart from the grey step details
                            const commentHtml = item.comments ?
                                `<p class="tw-mb-0 tw-mt-1.5 tw-flex tw-items-start tw-gap-2 tw-rounded-lg tw-border-0 tw-border-l-[3px] tw-border-solid tw-border-brand-500 tw-bg-brand-50 tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-leading-snug tw-text-brand-700"><i class="fas fa-comment-dots tw-mt-0.5 tw-text-brand-500" aria-hidden="true"></i><span class="tw-min-w-0 tw-break-words">${esc(item.comments)}</span></p>` :
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

            // --- STORE ADMIN SPECIFIC ACTIONS (Role 3; Super Admin / Admin at that step) ---
            if (canUseStoreAdminTools) {
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
                // Approved quantity: digits only, shown in the page language; never empty or zero.
                $(document).on('input', '.item-qty-input', function() {
                    const shown = localDigits(asciiDigits(this.value).replace(/[^0-9]/g, ''));
                    if (this.value !== shown) this.value = shown;
                });
                // Step arrows and the arrow keys change the quantity by one (never below 1).
                function stepQty(input, delta) {
                    const current = parseInt(asciiDigits(input.value), 10) || (delta > 0 ? 0 : 1);
                    input.value = localDigits(Math.min(99999, Math.max(1, current + delta)));
                    $(input).trigger('change');
                }
                $(document).on('click', '.item-qty-step', function() {
                    stepQty($(this).closest('span').parent().find('.item-qty-input')[0], Number($(this).data('step')));
                });
                $(document).on('keydown', '.item-qty-input', function(e) {
                    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        stepQty(this, e.key === 'ArrowUp' ? 1 : -1);
                    }
                });
                $(document).on('blur', '.item-qty-input', function() {
                    const number = parseInt(asciiDigits(this.value), 10);
                    this.value = localDigits(number > 0 ? number : 1);
                });

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
                            quantity: parseInt(asciiDigits($(this).val()), 10) || 1,
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
