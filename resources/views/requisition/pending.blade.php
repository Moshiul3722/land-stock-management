@extends('layouts.app')

@push('page_css')
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

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('requisition.pending_title') }}</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ __('requisition.pending_list') }}</h3>

                {{-- SCANNER TOOLS (ONLY FOR STORE KEEPER - Role 4) --}}
                @if (Auth::user()->role_id == 4)
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text" id="scan-qr-input" class="form-control float-right"
                                placeholder="Scan or Type ID...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-warning" id="btn-open-camera"
                                    title="Scan with Camera">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <table id="pending-req-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('lot.table_sl') }}</th>
                            <th>ID</th>
                            <th>Requestor</th>
                            <th>Date</th>
                            <th>Total Items</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingTasks as $task)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center font-weight-bold">{{ $task->requisition->id }}</td>
                                <td>{{ $task->requisition->requestedBy->name ?? 'N/A' }}</td>
                                <td>{{ $task->requisition->requisition_date->format('d-M-Y') }}</td>
                                <td class="text-center">{{ $task->requisition->items->count() }}</td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm view-action-btn"
                                        data-id="{{ $task->requisition->id }}"
                                        data-url="{{ route('requisitions.get_details', $task->requisition->id) }}">
                                        {{-- Conditional Text: Action for Approvers, View for Store Keeper --}}
                                        <i class="fa fa-eye"></i>
                                        {{ in_array(Auth::user()->role_id, [2, 3]) ? 'View & Action' : 'View' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ACTION MODAL --}}
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Requisition Details & Timeline</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        onclick="$('#actionModal').modal('hide')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 border-right">
                            {{-- Info --}}
                            <div class="row mb-3">
                                <div class="col-md-6"><strong>Requested By:</strong> <span id="modal-requestor"></span>
                                </div>
                                <div class="col-md-6 text-right"><strong>Date:</strong> <span id="modal-date"></span></div>
                            </div>

                            {{-- Action Header --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-primary mb-0">Items & Assignment</h6>

                                {{-- BUTTONS: ONLY FOR STORE ADMIN (3) --}}
                                @if (Auth::user()->role_id == 3)
                                    <div>
                                        <button type="button" id="btn-split-notify"
                                            class="btn btn-danger btn-sm shadow-sm mr-2" style="display: none;">
                                            <i class="fas fa-random"></i> Create New Req. & Notify
                                        </button>

                                        <button type="button" id="btn-update-qty" class="btn btn-warning btn-sm shadow-sm">
                                            <i class="fas fa-save"></i> Save Assignments & Qty
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <form id="items-form">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            {{-- Checkbox: Only for Store Admin (3) --}}
                                            @if (Auth::user()->role_id == 3)
                                                <th width="5%"><i class="fas fa-check-square"></i></th>
                                            @endif
                                            <th>{{ __('requisition.product') }}</th>
                                            <th>{{ __('requisition.cat_sub_cat') }}</th>
                                            <th width="35%">{{ __('requisition.assigned_lot') }}</th>
                                            <th width="80">{{ __('requisition.requested_quantity') }}</th>
                                            <th width="100">{{ __('requisition.approved_quantity') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-items-body"></tbody>
                                </table>
                            </form>

                            <div class="form-group mt-4">
                                <label>{{ __('requisition.approval_comments') }}</label>
                                <textarea class="form-control" id="action-comments" rows="3" placeholder="{{ __('requisition.enter_comments') }}"></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <h6 class="font-weight-bold text-primary mb-3 ml-2">{{ __('requisition.approval_history') }}</h6>
                            <div id="modal-timeline" class="timeline-container"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    {{-- Print: Visible for Admin (2) & Store Admin (3) --}}
                    @if (in_array(Auth::user()->role_id, [2, 3]))
                        <a href="#" id="btn-print" target="_blank" class="btn btn-default mr-auto"><i
                                class="fas fa-print"></i> {{ __('requisition.print_invoice') }}</a>
                    @endif

                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#actionModal').modal('hide')">{{ __('buttons.cancel') }}</button>

                    {{-- Actions: Visible for Admin (2) & Store Admin (3) --}}
                    @if (in_array(Auth::user()->role_id, [2, 3]))
                        <button type="button" class="btn btn-danger" id="btn-reject"><i class="fas fa-times"></i>
                            {{ __('buttons.reject') }}</button>
                        <button type="button" class="btn btn-success" id="btn-approve"><i class="fas fa-check"></i>
                            {{ __('buttons.approve') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- SCANNER MODAL (Only for Store Keeper) --}}
    @if (Auth::user()->role_id == 4)
        <div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-camera"></i> {{ __('requisition.scan_qr') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
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

@push('js')
    @if (Auth::user()->role_id == 4)
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    @endif
    <script>
        let html5QrcodeScanner = null;

        // --- SCANNER LOGIC (Store Keeper) ---
        function onScanSuccess(decodedText, decodedResult) {
            let cleanVal = decodedText.split('?')[0];
            let parts = cleanVal.split('/');
            let id = parts[parts.length - 1];

            if ($.isNumeric(id)) {
                let table = $('#pending-req-table').DataTable();
                table.search(id).draw();
                toastr.success('Found Requisition #' + id);
                stopScanner();
                $('#scannerModal').modal('hide');
                processRequisitionViaScan(id);
            } else {
                toastr.warning('Invalid QR Code Format');
            }
        }

        function processRequisitionViaScan(id) {
            Swal.fire({
                title: 'Processing Requisition #' + id,
                text: "Verifying and releasing stock...",
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
                                    title: 'Success!',
                                    text: res.message,
                                    timer: 2000
                                })
                                .then(() => {
                                    location.reload();
                                });
                        },
                        error: function(err) {
                            let msg = err.responseJSON ? err.responseJSON.message :
                                'Processing Failed';
                            Swal.fire('Error', msg, 'error');
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
            var table = $('#pending-req-table').DataTable({
                "order": [
                    [1, "desc"]
                ]
            });
            let currentRequisitionId = null;
            let totalItemsInModal = 0;

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
                            toastr.error('Invalid QR Code');
                        }
                    }
                });
            }

            // --- VIEW MODAL ---
            $('#pending-req-table tbody').on('click', '.view-action-btn', function() {
                let url = $(this).data('url');
                currentRequisitionId = $(this).data('id');

                $('#actionModal').modal('show');
                $('#btn-split-notify').hide();

                // Read-Only for Store Keeper (Role 4)
                if (isStoreKeeper) {
                    $('#action-comments').prop('disabled', true).val('');
                } else {
                    $('#action-comments').prop('disabled', false).val('');
                    let printUrl = "{{ route('requisitions.print', ':id') }}".replace(':id',
                        currentRequisitionId);
                    $('#btn-print').attr('href', printUrl);
                }

                $('#modal-timeline').html('<div class="text-center p-3">Loading...</div>');
                $('#modal-items-body').html(
                    '<tr><td colspan="6" class="text-center">Loading items...</td></tr>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(res.formatted_date);
                        totalItemsInModal = res.requisition.items.length;

                        let itemsHtml = '';
                        res.requisition.items.forEach(item => {
                            let assignmentHtml =
                                '<span class="text-muted small font-italic">Pending Assignment</span>';
                            let isUnavailable = false;

                            if (isStoreAdmin) {
                                // STORE ADMIN (Role 3): Editable Dropdowns
                                let options =
                                    '<option value="">-- Select Brand/Spec --</option>';
                                if (item.available_lots && item.available_lots.length >
                                    0) {
                                    item.available_lots.forEach(lot => {
                                        let selected = (item.lot_detail_id ==
                                                lot.lot_detail_id) ?
                                            'selected' : '';
                                        let label =
                                            `${lot.brand || 'N/A'} - ${lot.specification || 'N/A'} (Qty: ${lot.quantity})`;
                                        options +=
                                            `<option value="${lot.lot_detail_id}" ${selected}>${label}</option>`;
                                    });
                                    assignmentHtml =
                                        `<select class="form-control form-control-sm item-lot-select" data-id="${item.id}">${options}</select>`;
                                } else {
                                    assignmentHtml =
                                        '<span class="text-danger small font-weight-bold stock-unavailable">Stock Unavailable</span>';
                                    isUnavailable = true;
                                }
                            } else if (item.lot_detail_id) {
                                // ADMIN (Role 2) or Store Keeper: Read-Only Assigned Text
                                let foundLot = item.available_lots ? item.available_lots
                                    .find(l => l.lot_detail_id == item.lot_detail_id) :
                                    null;
                                let label = foundLot ?
                                    `${foundLot.brand || ''} ${foundLot.specification || ''}` :
                                    'Assigned';
                                assignmentHtml =
                                    `<span class="text-success small font-weight-bold"><i class="fas fa-check"></i> ${label}</span>`;
                            } else {
                                assignmentHtml =
                                    '<span class="text-danger small font-weight-bold">Not Assigned</span>';
                            }

                            // Edit permissions for Qty Input
                            let disabledAttr = isStoreAdmin ? '' : 'disabled';
                            let inputClass = isStoreAdmin ?
                                'form-control form-control-sm item-qty-input text-center' :
                                'form-control-plaintext text-center font-weight-bold';

                            let subCatName = '';
                            if (item.product.sub_category) subCatName = item.product
                                .sub_category.name;
                            else if (item.product.subCategory) subCatName = item.product
                                .subCategory.name;
                            let categoryDisplay = item.product.category.name;
                            if (subCatName) categoryDisplay +=
                                `<br><small class="text-muted">${subCatName}</small>`;

                            // Checkbox Logic (Role 3 Only)
                            let checkboxHtml = '';
                            if (isStoreAdmin) {
                                if (isUnavailable) {
                                    checkboxHtml =
                                        `<td class="text-center"><input type="checkbox" class="split-item-checkbox" value="${item.id}"></td>`;
                                } else {
                                    checkboxHtml = `<td></td>`;
                                }
                            }

                            itemsHtml += `
                            <tr>
                                ${checkboxHtml}
                                <td>${item.product.name}</td>
                                <td>${categoryDisplay}</td>
                                <td>${assignmentHtml}</td>
                                <td class="text-center font-weight-bold text-muted">${item.original_quantity || item.quantity}</td>
                                <td>
                                    <input type="number"
                                           class="${inputClass}"
                                           data-id="${item.id}"
                                           value="${item.quantity}"
                                           min="1"
                                           ${disabledAttr}>
                                </td>
                            </tr>`;
                        });
                        $('#modal-items-body').html(itemsHtml);

                        if (isStoreAdmin) evaluateApproveButtonFromUI();

                        // Timeline
                        let timelineHtml = '';
                        res.timeline.forEach(item => {
                            let colorClass = item.color || 'secondary';
                            if (colorClass === 'warning') colorClass = 'custom-warning';
                            let bgClass = 'bg-' + colorClass;
                            let commentHtml = item.comments ?
                                `<div class="timeline-comment">"${item.comments}"</div>` :
                                '';
                            timelineHtml += `
                            <div class="timeline-entry">
                                <div class="timeline-number ${bgClass}">${item.step}</div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="user-name">${item.user}</span>
                                        <span class="action-date">${item.date}</span>
                                    </div>
                                    <span class="role-badge">${item.role}</span>
                                    <span class="text-${item.color} font-weight-bold small ml-1">(${item.title})</span>
                                    ${commentHtml}
                                </div>
                            </div>`;
                        });
                        $('#modal-timeline').html(timelineHtml);
                    },
                    error: function() {
                        toastr.error("Failed to load details");
                    }
                });
            });

            // --- STORE ADMIN SPECIFIC ACTIONS (Role 3) ---
            if (isStoreAdmin) {
                // Split Button Toggle
                $(document).on('change', '.split-item-checkbox', function() {
                    let checkedCount = $('.split-item-checkbox:checked').length;
                    let btn = $('#btn-split-notify');
                    if (checkedCount > 0) {
                        btn.fadeIn();
                        if (checkedCount === totalItemsInModal) {
                            btn.removeClass('btn-danger').addClass('btn-info').html(
                                '<i class="fas fa-bell"></i> Notify for Stock (No Split)');
                        } else {
                            btn.removeClass('btn-info').addClass('btn-danger').html(
                                '<i class="fas fa-random"></i> Create New Req. & Notify');
                        }
                    } else {
                        btn.fadeOut();
                    }
                });

                // Split Action
                $('#btn-split-notify').click(function() {
                    let selectedItemIds = [];
                    $('.split-item-checkbox:checked').each(function() {
                        selectedItemIds.push($(this).val());
                    });
                    if (selectedItemIds.length === 0) return;

                    let isFull = (selectedItemIds.length === totalItemsInModal);
                    let title = isFull ? 'Notify Store Keeper?' : 'Create New Req?';
                    let text = isFull ? "All items unavailable. Store Keeper will be notified." :
                        "Selected items will move to new requisition.";
                    let btnText = isFull ? 'Yes, Notify' : 'Yes, Split';
                    let url = "{{ route('requisitions.split', ':id') }}".replace(':id',
                        currentRequisitionId);

                    Swal.fire({
                            title: title,
                            text: text,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            confirmButtonText: btnText
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
                                        toastr.error('Error: ' + xhr.responseJSON.message);
                                    }
                                });
                            }
                        });
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
                    btn.html('Saving...').prop('disabled', true);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            items: itemsData
                        },
                        success: function(res) {
                            toastr.success(res.message);
                            evaluateApproveButtonFromUI();
                        },
                        error: function() {
                            toastr.error("Failed to update.");
                        },
                        complete: function() {
                            btn.html('<i class="fas fa-save"></i> Save Assignments & Qty').prop(
                                'disabled', false);
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
                            toastr.warning(
                                "Cannot approve. Some items are unavailable. Please split or resolve them first."
                                );
                            return;
                        }
                        if ($('.item-lot-select').filter((_, el) => !$(el).val()).length > 0) {
                            toastr.warning("Please assign a Brand/Spec to all items before approving.");
                            return;
                        }
                    }

                    Swal.fire({
                        title: 'Approve?',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Yes'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            let form = $('#approve-form');
                            form.attr('action', url);
                            form.html(
                                `@csrf <input type="hidden" name="comments" value="${comments}">`
                                );
                            form.submit();
                        }
                    });
                });

                // Reject
                $('#btn-reject').click(function() {
                    let url = "{{ route('requisitions.reject', ':id') }}".replace(':id',
                        currentRequisitionId);
                    let comments = $('#action-comments').val();
                    if (!comments) {
                        toastr.error("Comments required");
                        return;
                    }
                    Swal.fire({
                        title: 'Reject?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            let form = $('#reject-form');
                            form.attr('action', url);
                            form.html(
                                `@csrf <input type="hidden" name="comments" value="${comments}">`
                                );
                            form.submit();
                        }
                    });
                });
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
                    btn.prop('disabled', false).attr('title', 'Ready to approve');
                } else {
                    btn.prop('disabled', true).attr('title',
                    'Resolve unavailable items or assign stock to proceed');
                }
            }
        });
    </script>
@endpush
