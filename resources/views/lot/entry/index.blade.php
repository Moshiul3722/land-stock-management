@extends('layouts.app')

@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
    $showDate = fn($date) => $date ? ($isEn ? $date->format('d M, Y') : bangla_date($date->timestamp, 'en')) : '—';
    $notAvailable = __('lot.requests.not_available');
@endphp

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.pending_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.list') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">{{ __('lot.pending_list') }}</h3>
            </div>

            <div class="card-body">
                {{-- Toolbar: page size and search share one row --}}
                <div class="form-row align-items-end mb-3">
                    <div class="col-5 col-md-3 col-lg-2">
                        <label for="lot_req_page_length" class="mb-1">{{ __('commontable.per_page') }}</label>
                        <select id="lot_req_page_length" class="custom-select">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-9 col-lg-10 mt-2 mt-md-0">
                        <label for="lot_req_search" class="mb-1">{{ __('commontable.search') }}</label>
                        <input type="search" id="lot_req_search" class="form-control" autocomplete="off"
                            placeholder="{{ __('lot.requests.search_placeholder') }}"
                            title="{{ __('lot.requests.search_placeholder') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="table-responsive col-12">
                        <table class="table" id="pending-lots-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center lot-req-serial">{{ __('commontable.serial') }}</th>
                                    <th class="text-center">{{ __('lot.table_lot_number') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ __('lot.purchase_type') }}</th>
                                    <th class="text-center d-none d-lg-table-cell">{{ __('lot.table_submitted_by') }}</th>
                                    <th class="text-center d-none d-sm-table-cell">{{ __('lot.table_entry_date') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ __('lot.table_status') }}</th>
                                    <th class="text-center lot-req-actions">{{ __('lot.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingLots as $lot)
                                    @php
                                        $purchaseType = $lot->purchaseType->name ?? $notAvailable;
                                        $submittedBy = $lot->approvalDetails->first()->fromUser->name ?? $notAvailable;
                                        $entryDate = $showDate($lot->entry_date);
                                        $statusLabel = __('lot.manage.status.' . $lot->status);
                                        // Everything the items dialog shows, already localized.
                                        $payload = [
                                            'lotNumber' => $digits($lot->lot_number),
                                            'purchaseType' => $purchaseType,
                                            'fiscalYear' => $lot->fiscalYear ? $digits($lot->fiscalYear->name) : $notAvailable,
                                            'entryDate' => $entryDate,
                                            'submittedBy' => $submittedBy,
                                            'remarks' => (string) $lot->remarks,
                                            'approveUrl' => route('lots.approve', $lot),
                                            'rejectUrl' => route('lots.reject', $lot),
                                            'items' => $lot->lotDetails->map(fn($detail) => [
                                                'name' => $detail->product->name ?? $notAvailable,
                                                'note' => collect([$detail->brand, $detail->specification])->filter()->join(' · '),
                                                'category' => $detail->product->category->name ?? '—',
                                                'quantity' => $digits($detail->quantity + 0),
                                                'unit' => $detail->product && ! is_numeric($detail->product->product_unit) ? (string) $detail->product->product_unit : '',
                                            ])->values(),
                                        ];
                                    @endphp
                                    <tr>
                                        {{-- Serial number: refreshed by the table script after sorting, search and paging --}}
                                        <td class="text-center lot-req-serial">{{ $digits($loop->iteration) }}</td>
                                        <td class="text-center lot-req-wrap" data-order="{{ $lot->lot_number }}"
                                            data-search="{{ $lot->lot_number }} {{ $digits($lot->lot_number) }}">
                                            <span class="font-weight-bold">{{ $digits($lot->lot_number) }}</span>
                                            {{-- Small screens: the hidden columns are folded in under the lot number --}}
                                            <small class="d-block d-md-none text-muted">{{ $purchaseType }}</small>
                                            <small class="d-block d-sm-none text-muted">{{ $entryDate }}</small>
                                        </td>
                                        <td class="text-center d-none d-md-table-cell">{{ $purchaseType }}</td>
                                        <td class="text-center lot-req-wrap d-none d-lg-table-cell">{{ $submittedBy }}</td>
                                        <td class="text-center text-nowrap d-none d-sm-table-cell"
                                            data-order="{{ $lot->entry_date?->timestamp ?? 0 }}">{{ $entryDate }}</td>
                                        <td class="text-center d-none d-md-table-cell">
                                            <span class="badge badge-warning lot-req-status">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="text-center text-nowrap lot-req-actions">
                                            <button type="button" class="btn btn-sm btn-info lot-req-btn view-lot-btn"
                                                data-lot='@json($payload, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)'
                                                title="{{ __('lot.requests.view') }}"
                                                aria-label="{{ __('lot.requests.view') }}: {{ $digits($lot->lot_number) }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success lot-req-btn approve-btn"
                                                data-action="{{ route('lots.approve', $lot) }}"
                                                title="{{ __('lot.requests.approve') }}"
                                                aria-label="{{ __('lot.requests.approve') }}: {{ $digits($lot->lot_number) }}">
                                                <i class="fas fa-check" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger lot-req-btn reject-btn"
                                                data-action="{{ route('lots.reject', $lot) }}"
                                                title="{{ __('lot.requests.reject') }}"
                                                aria-label="{{ __('lot.requests.reject') }}: {{ $digits($lot->lot_number) }}">
                                                <i class="fas fa-times" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items of one lot --}}
    <div class="modal fade" id="lotItemsModal" tabindex="-1" role="dialog" aria-labelledby="lotItemsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-heading">
                        <h5 class="modal-title" id="lotItemsModalLabel">
                            {{ __('lot.requests.items_title') }}: <span id="lot_items_number"></span>
                        </h5>
                        <p class="modal-hint">{{ __('lot.requests.items_hint') }}</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('commontable.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="lot-items-meta">
                        <div>
                            <dt>{{ __('lot.purchase_type') }}</dt>
                            <dd id="lot_items_purchase_type"></dd>
                        </div>
                        <div>
                            <dt>{{ __('lot.fiscal_year') }}</dt>
                            <dd id="lot_items_fiscal_year"></dd>
                        </div>
                        <div>
                            <dt>{{ __('lot.table_entry_date') }}</dt>
                            <dd id="lot_items_entry_date"></dd>
                        </div>
                        <div>
                            <dt>{{ __('lot.table_submitted_by') }}</dt>
                            <dd id="lot_items_submitted_by"></dd>
                        </div>
                    </dl>
                    <p class="lot-items-remarks d-none" id="lot_items_remarks_wrap">
                        <strong>{{ __('lot.remarks') }}:</strong> <span id="lot_items_remarks"></span>
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0" id="lot_items_table">
                            <thead>
                                <tr>
                                    <th class="text-center lot-items-serial">{{ __('commontable.serial') }}</th>
                                    <th class="text-left">{{ __('lot.manage.item_description') }}</th>
                                    <th class="text-center d-none d-sm-table-cell">{{ __('lot.manage.category') }}</th>
                                    <th class="text-center lot-items-qty">{{ __('lot.table_quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <p class="text-center text-muted my-3 d-none" id="lot_items_empty">{{ __('lot.requests.no_items') }}</p>
                </div>
                {{-- Entry count + pagination: stays in view under the list; only when there are more than ten items --}}
                <nav class="lot-items-pager d-none" id="lot_items_pager" aria-label="{{ __('commontable.pagination') }}">
                    <span class="lot-items-info" id="lot_items_info" aria-live="polite"></span>
                    <ul class="pagination pagination-sm mb-0" id="lot_items_pages"></ul>
                </nav>
                <div class="modal-footer lot-items-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('commontable.close') }}</button>
                    <button type="button" class="btn btn-danger" id="lot_items_reject">
                        <i class="fas fa-times" aria-hidden="true"></i> {{ __('lot.requests.reject') }}
                    </button>
                    <button type="button" class="btn btn-success" id="lot_items_approve">
                        <i class="fas fa-check" aria-hidden="true"></i> {{ __('lot.requests.approve') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Submitted by the approve / reject confirmation --}}
    <form id="approval-form" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="comments" id="swal-comments">
    </form>
@endsection

@push('page_css')
    <style>
        .lot-req-wrap {
            overflow-wrap: break-word;
        }

        /* Compact rows */
        #pending-lots-table td {
            vertical-align: middle;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
            line-height: 1.35;
        }

        #pending-lots-table thead th {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        #pending-lots-table small {
            font-size: 0.875rem;
        }

        #pending-lots-table .lot-req-serial {
            width: 64px;
            white-space: nowrap;
        }

        #pending-lots-table .lot-req-actions {
            width: 140px;
        }

        .lot-req-status {
            font-size: 0.85em;
            font-weight: 600;
            padding: 0.3em 0.6em;
        }

        .lot-req-btn {
            min-width: 34px;
            min-height: 34px;
            padding: 0.2rem 0.45rem;
        }

        /* Table footer (entry count + pagination) sits on its own tinted band */
        #pending-lots-table_wrapper > .row:last-child {
            margin: 0.75rem 0 0;
            padding: 0.6rem 0.75rem;
            align-items: center;
            background: #e9eefb;
            border: 1px solid #cfd9f3;
            border-radius: 0.5rem;
        }

        #pending-lots-table_wrapper > .row:last-child .dt-info {
            padding-top: 0;
            color: #1f2d5a;
            font-weight: 600;
        }

        #pending-lots-table_wrapper > .row:last-child .pagination {
            margin-bottom: 0;
        }

        /* Items dialog */
        .lot-items-meta {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.5rem 1rem;
            margin: 0 0 0.75rem;
            padding: 0.6rem 0.75rem;
            background: #f8f9fc;
            border: 1px solid #e3e8f4;
            border-radius: 0.5rem;
        }

        .lot-items-meta dt {
            font-size: 0.8em;
            font-weight: 600;
            color: #64748b;
        }

        .lot-items-meta dd {
            margin: 0;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .lot-items-remarks {
            margin-bottom: 0.75rem;
            overflow-wrap: anywhere;
        }

        #lot_items_table th,
        #lot_items_table td {
            padding: 0.3rem 0.6rem;
            vertical-align: middle;
        }

        #lot_items_table .lot-items-serial {
            width: 64px;
            white-space: nowrap;
        }

        #lot_items_table .lot-items-qty {
            width: 120px;
        }

        #lot_items_table td small {
            display: block;
            color: #64748b;
        }

        /* Pager of the items dialog: a tinted band between the list and the buttons */
        .lot-items-pager {
            display: flex;
            flex: 0 0 auto;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.4rem 0.75rem;
            margin: 0 1rem 0.75rem;
            padding: 0.45rem 0.75rem;
            background: #e9eefb;
            border: 1px solid #cfd9f3;
            border-radius: 0.5rem;
        }

        .lot-items-info {
            color: #1f2d5a;
            font-size: 0.9em;
            font-weight: 600;
        }

        .lot-items-pager .page-link {
            cursor: pointer;
        }

        @media (max-width: 767.98px) {
            .lot-items-pager {
                justify-content: center;
                text-align: center;
            }

            #pending-lots-table_wrapper .dt-info,
            #pending-lots-table_wrapper .dt-paging {
                text-align: center;
                float: none;
                margin-top: 0.25rem;
            }

            #pending-lots-table_wrapper .dt-paging .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }

            .lot-items-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            #pending-lots-table th,
            #pending-lots-table td {
                padding-left: 0.3rem;
                padding-right: 0.3rem;
            }

            #pending-lots-table .lot-req-serial {
                width: 40px;
            }

            #pending-lots-table .lot-req-actions {
                width: 128px;
            }

            /* Finger-sized buttons and fields on phones */
            .lot-req-btn {
                min-width: 38px;
                min-height: 38px;
            }

            #lot_req_page_length,
            #lot_req_search {
                min-height: 44px;
                font-size: 16px; /* stops iOS zooming into the field */
            }

            #lot_items_table .lot-items-serial {
                width: 40px;
            }

            #lot_items_table .lot-items-qty {
                width: 84px;
            }

            .lot-items-footer {
                flex-direction: column-reverse;
            }

            .lot-items-footer > .btn {
                width: 100%;
                margin: 0.25rem 0;
                min-height: 44px;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(function() {
            var text = {
                approveTitle: @json(__('lot.requests.approve_title')),
                approveText: @json(__('lot.requests.approve_text')),
                approveYes: @json(__('lot.requests.approve_yes')),
                rejectTitle: @json(__('lot.requests.reject_title')),
                rejectText: @json(__('lot.requests.reject_text')),
                rejectPlaceholder: @json(__('lot.requests.reject_placeholder')),
                rejectYes: @json(__('lot.requests.reject_yes')),
                reasonRequired: @json(__('lot.requests.reject_reason_required')),
                cancel: @json(__('sweetalert.cancel')),
                failedTitle: @json(__('lot.requests.failed_title')),
                okay: @json(__('sweetalert.okay'))
            };

            function toast(type, message) {
                (window.appToast || window.toastr)[type](message);
            }

            // ---- Result of the last approve / reject ----
            @if (session('success'))
                toast('success', @json(session('success')));
            @endif
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: text.failedTitle,
                    text: @json(session('error')),
                    confirmButtonText: text.okay
                });
            @endif

            // ---- Table ----
            var useBanglaDigits = @json(!$isEn);
            var banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

            function localDigits(value) {
                var out = String(value);
                return useBanglaDigits ? out.replace(/[0-9]/g, function(d) {
                    return banglaDigits[d];
                }) : out;
            }

            var table = $('#pending-lots-table').DataTable({
                paging: true,
                pageLength: 10,
                searching: true,
                bDestroy: true,
                autoWidth: false,
                order: [], // keep the server order: oldest request first
                columnDefs: [{
                    orderable: false,
                    searchable: false,
                    targets: 0
                }, {
                    orderable: false,
                    searchable: false,
                    targets: -1
                }],
                layout: {
                    topStart: null,
                    topEnd: null,
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                formatNumber: function(number) {
                    return localDigits(number);
                },
                language: {
                    info: @json(__('commontable.dt_info')),
                    infoEmpty: @json(__('commontable.dt_info_empty')),
                    infoFiltered: @json(__('commontable.dt_info_filtered')),
                    zeroRecords: @json(__('commontable.dt_zero_records')),
                    emptyTable: @json(__('commontable.no_record_found')),
                    paginate: {
                        first: '«',
                        previous: '‹',
                        next: '›',
                        last: '»'
                    },
                    aria: {
                        paginate: {
                            first: @json(__('commontable.dt_first')),
                            previous: @json(__('commontable.dt_previous')),
                            next: @json(__('commontable.dt_next')),
                            last: @json(__('commontable.dt_last'))
                        }
                    }
                }
            });

            // Serial numbers follow the current sort, search and page.
            function renumber() {
                var start = table.page.info().start;
                table.rows({
                    search: 'applied',
                    order: 'applied',
                    page: 'current'
                }).nodes().each(function(row, index) {
                    $(row).children('td').first().text(localDigits(start + index + 1));
                });
            }
            table.on('draw', renumber);
            renumber();

            $('#lot_req_page_length').on('change', function() {
                table.page.len(parseInt($(this).val(), 10) || 10).draw();
            });
            $('#lot_req_search').on('input', function() {
                table.search(this.value).draw();
            });

            // ---- Approve / reject ----
            var approvalForm = $('#approval-form');

            function submitDecision(actionUrl, comments) {
                $('#swal-comments').val(comments);
                approvalForm.attr('action', actionUrl);
                $('#wait').show();
                approvalForm.trigger('submit');
            }

            function askApprove(actionUrl) {
                Swal.fire({
                    title: text.approveTitle,
                    text: text.approveText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: text.approveYes,
                    cancelButtonText: text.cancel,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        submitDecision(actionUrl, '');
                    }
                });
            }

            function askReject(actionUrl) {
                Swal.fire({
                    title: text.rejectTitle,
                    text: text.rejectText,
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: text.rejectPlaceholder,
                    showCancelButton: true,
                    confirmButtonText: text.rejectYes,
                    cancelButtonText: text.cancel,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    preConfirm: function(comments) {
                        if (!comments || !comments.trim()) {
                            Swal.showValidationMessage(text.reasonRequired);
                        }
                        return comments;
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        submitDecision(actionUrl, result.value);
                    }
                });
            }

            $('#pending-lots-table').on('click', '.approve-btn', function() {
                askApprove($(this).data('action'));
            });
            $('#pending-lots-table').on('click', '.reject-btn', function() {
                askReject($(this).data('action'));
            });

            // ---- Items dialog ----
            var itemsModal = $('#lotItemsModal');
            var currentLot = null;
            var ITEMS_PER_PAGE = 10;
            var itemsPage = 1;
            var pagerText = {
                info: @json(__('commontable.dt_info')),
                previous: @json(__('commontable.dt_previous')),
                next: @json(__('commontable.dt_next'))
            };

            function pageButton(label, page, options) {
                var item = $('<li class="page-item">')
                    .toggleClass('disabled', !!options.disabled)
                    .toggleClass('active', !!options.active);
                var button = $('<button type="button" class="page-link">').text(label)
                    .attr('data-page', page).prop('disabled', !!options.disabled);
                if (options.label) {
                    button.attr('aria-label', options.label);
                }
                if (options.active) {
                    button.attr('aria-current', 'page');
                }
                return item.append(button);
            }

            // Ten items per page; the pager appears only when there are more.
            function renderItems() {
                var items = currentLot ? currentLot.items : [];
                var total = items.length;
                var last = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));
                itemsPage = Math.min(Math.max(1, itemsPage), last);
                var start = (itemsPage - 1) * ITEMS_PER_PAGE;
                var pageItems = items.slice(start, start + ITEMS_PER_PAGE);

                var body = $('#lot_items_table tbody').empty();
                $.each(pageItems, function(index, item) {
                    var description = $('<td class="text-left lot-req-wrap">').text(item.name);
                    if (item.note) {
                        description.append($('<small>').text(item.note));
                    }
                    // Phones: the category column is folded in under the name.
                    description.append($('<small class="d-sm-none">').text(item.category));

                    var quantity = $('<td class="text-center text-nowrap">').text(item.quantity);
                    if (item.unit) {
                        quantity.append(' ').append($('<small class="d-inline">').text(item.unit));
                    }

                    body.append($('<tr>')
                        .append($('<td class="text-center lot-items-serial">').text(localDigits(start + index + 1)))
                        .append(description)
                        .append($('<td class="text-center d-none d-sm-table-cell">').text(item.category))
                        .append(quantity));
                });

                $('#lot_items_table').toggleClass('d-none', total === 0);
                $('#lot_items_empty').toggleClass('d-none', total !== 0);

                $('#lot_items_pager').toggleClass('d-none', last <= 1);
                if (last > 1) {
                    $('#lot_items_info').text(pagerText.info
                        .replace('_TOTAL_', localDigits(total))
                        .replace('_START_', localDigits(start + 1))
                        .replace('_END_', localDigits(start + pageItems.length)));

                    var pages = $('#lot_items_pages').empty();
                    pages.append(pageButton('‹', itemsPage - 1, { disabled: itemsPage === 1, label: pagerText.previous }));
                    for (var page = Math.max(1, itemsPage - 2); page <= Math.min(last, itemsPage + 2); page++) {
                        pages.append(pageButton(localDigits(page), page, { active: page === itemsPage }));
                    }
                    pages.append(pageButton('›', itemsPage + 1, { disabled: itemsPage === last, label: pagerText.next }));
                }
            }

            $('#lot_items_pages').on('click', '.page-link', function() {
                itemsPage = parseInt($(this).attr('data-page'), 10) || 1;
                renderItems();
                $('#lotItemsModal .modal-body').scrollTop(0);
            });

            $('#pending-lots-table').on('click', '.view-lot-btn', function() {
                var lot = $(this).data('lot');
                currentLot = lot;

                $('#lot_items_number').text(lot.lotNumber);
                $('#lot_items_purchase_type').text(lot.purchaseType);
                $('#lot_items_fiscal_year').text(lot.fiscalYear);
                $('#lot_items_entry_date').text(lot.entryDate);
                $('#lot_items_submitted_by').text(lot.submittedBy);
                $('#lot_items_remarks').text(lot.remarks);
                $('#lot_items_remarks_wrap').toggleClass('d-none', !lot.remarks);

                itemsPage = 1;
                renderItems();

                itemsModal.modal('show');
            });

            // The decision dialogs open after the items dialog has closed.
            function decideFromModal(ask, urlKey) {
                if (!currentLot) {
                    return;
                }
                var url = currentLot[urlKey];
                itemsModal.one('hidden.bs.modal', function() {
                    ask(url);
                });
                itemsModal.modal('hide');
            }

            $('#lot_items_approve').on('click', function() {
                decideFromModal(askApprove, 'approveUrl');
            });
            $('#lot_items_reject').on('click', function() {
                decideFromModal(askReject, 'rejectUrl');
            });
        });
    </script>
@endpush
