@extends('layouts.app')

@section('styles')
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

        .bg-primary {
            background-color: #007bff !important;
        }

        .bg-success {
            background-color: #28a745 !important;
        }

        .bg-danger {
            background-color: #dc3545 !important;
        }

        .bg-warning {
            background-color: #ffc107 !important;
            color: #333 !important;
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

        .timeline-comment strong {
            display: block;
            font-size: 0.75rem;
            color: #17a2b8;
            margin-bottom: 2px;
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
    {{-- Import HTML5-QRCode Library --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
@endsection

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
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <input type="text" id="scan-qr-input" class="form-control float-right"
                            placeholder="Scan or Type ID...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-warning" id="btn-open-camera" title="Scan with Camera">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>
                </div>
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
                                        <i class="fa fa-eye"></i> View & Action
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
                        {{-- LEFT: Details --}}
                        <div class="col-md-8 border-right">
                            <div class="row mb-3">
                                <div class="col-md-6"><strong>Requested By:</strong> <span id="modal-requestor"></span>
                                </div>
                                <div class="col-md-6 text-right"><strong>Date:</strong> <span id="modal-date"></span></div>
                            </div>

                            {{-- Items Table --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-primary mb-0">Items & Assignment</h6>

                                {{-- 
                                    Update Button: Only for Store Admin (Role 3)
                                --}}
                                @if (Auth::user()->role_id == 3)
                                    <button type="button" id="btn-update-qty" class="btn btn-warning btn-sm shadow-sm">
                                        <i class="fas fa-save"></i> Save Assignments & Qty
                                    </button>
                                @endif
                            </div>

                            <form id="items-form">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th>Product</th>
                                            <th>Category / Sub Category</th>
                                            <th width="35%">Assigned Lot (Brand/Spec)</th>
                                            <th width="80">Orig. Qty</th>
                                            <th width="100">Req. Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-items-body"></tbody>
                                </table>
                            </form>

                            <div class="form-group mt-4">
                                <label>Your Comments (Required for Rejection)</label>
                                <textarea class="form-control" id="action-comments" rows="3" placeholder="Enter comments..."></textarea>
                            </div>
                        </div>

                        {{-- RIGHT: Timeline --}}
                        <div class="col-md-4">
                            <h6 class="font-weight-bold text-primary mb-3 ml-2">Approval History</h6>
                            <div id="modal-timeline" class="timeline-container"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    {{-- Print Button --}}
                    <a href="#" id="btn-print" target="_blank" class="btn btn-default mr-auto">
                        <i class="fas fa-print"></i> Print Slip
                    </a>

                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#actionModal').modal('hide')">Close</button>

                    {{-- Reject Button: Hidden for Store Keeper (Role 4) --}}
                    @if (Auth::user()->role_id != 4)
                        <button type="button" class="btn btn-danger" id="btn-reject"><i class="fas fa-times"></i>
                            Reject</button>
                    @endif

                    {{-- Approve/Process Button --}}
                    <button type="button" class="btn btn-success" id="btn-approve">
                        <i class="fas fa-check"></i> {{ Auth::user()->role_id == 4 ? 'Process & Complete' : 'Approve' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCANNER MODAL --}}
    <div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-camera"></i> Scan QR Code</h5>
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
                        data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Forms --}}
    <form id="approve-form" method="POST" style="display:none;">@csrf</form>
    <form id="reject-form" method="POST" style="display:none;">@csrf</form>
@endsection

@push('js')
    <script>
        let html5QrcodeScanner = null;

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
            } else {
                toastr.warning('Invalid QR Code Format');
            }
        }

        function onScanFailure(error) {}

        function startScanner() {
            $('#scannerModal').modal('show');
            if (html5QrcodeScanner === null) {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    }, false);
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(_ => {
                    html5QrcodeScanner = null;
                }).catch(error => {
                    console.error("Failed to clear html5QrcodeScanner.");
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

            // --- ROLE CHECKS ---
            const isStoreAdmin = {{ Auth::user()->role_id == 3 ? 'true' : 'false' }};
            const isStoreKeeper = {{ Auth::user()->role_id == 4 ? 'true' : 'false' }};

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
                        table.search(id).draw();
                        $(this).val('');
                        toastr.info('Filtered for Requisition #' + id);
                    } else {
                        toastr.error('Invalid QR Code');
                    }
                }
            });

            // 1. View & Load Data
            $('#pending-req-table tbody').on('click', '.view-action-btn', function() {
                let url = $(this).data('url');
                currentRequisitionId = $(this).data('id');

                $('#actionModal').modal('show');
                $('#modal-timeline').html('<div class="text-center p-3">Loading...</div>');
                let printUrl = "{{ route('requisitions.print', ':id') }}".replace(':id',
                    currentRequisitionId);
                $('#btn-print').attr('href', printUrl);

                $('#modal-items-body').html(
                    '<tr><td colspan="5" class="text-center">Loading items...</td></tr>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(res.formatted_date);

                        let itemsHtml = '';
                        res.requisition.items.forEach(item => {
                            let assignmentHtml =
                                '<span class="text-muted small font-italic">Pending Assignment</span>';

                            if (isStoreAdmin) {
                                // Store Admin: Shows Editable Dropdown to Assign Lots
                                let options =
                                    '<option value="">-- Select Brand/Spec --</option>';
                                if (item.available_lots && item.available_lots.length >
                                    0) {
                                    item.available_lots.forEach(lot => {
                                        // Use lot_detail_id as value
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
                                }
                            } else if (item.lot_detail_id) {
                                // Store Keeper / Others: Shows READ-ONLY Assigned Text
                                // Find the details in available_lots array to display name
                                let foundLot = item.available_lots ? item.available_lots
                                    .find(l => l.lot_detail_id == item.lot_detail_id) :
                                    null;
                                let label = foundLot ?
                                    `${foundLot.brand || ''} ${foundLot.specification || ''}` :
                                    'Assigned';
                                assignmentHtml =
                                    `<span class="text-success small font-weight-bold"><i class="fas fa-check"></i> ${label}</span>`;
                            } else {
                                // Edge case: User is not admin, and no lot assigned yet
                                assignmentHtml =
                                    '<span class="text-danger small font-weight-bold">Not Assigned</span>';
                            }

                            // Input Logic: Store Admin = Editable, Others = Readonly
                            let disabledAttr = isStoreAdmin ? '' : 'disabled';
                            let inputClass = isStoreAdmin ?
                                'form-control form-control-sm item-qty-input text-center' :
                                'form-control-plaintext text-center font-weight-bold';

                            // Sub Category Display Logic
                            let subCatName = '';
                            if (item.product.sub_category) subCatName = item.product
                                .sub_category.name;
                            else if (item.product.subCategory) subCatName = item.product
                                .subCategory.name;
                            let categoryDisplay = item.product.category.name;
                            if (subCatName) categoryDisplay +=
                                `<br><small class="text-muted">${subCatName}</small>`;

                            itemsHtml += `
                            <tr>
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

                        // Evaluate button state immediately
                        evaluateApproveButtonFromUI();

                        // Timeline
                        let timelineHtml = '';
                        res.timeline.forEach(item => {
                            let bgClass = 'bg-' + (item.color || 'secondary');
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

            // 2. Update Quantity (Store Admin Only)
            $('#btn-update-qty').click(function() {
                let itemsData = {};
                $('.item-qty-input').each(function() {
                    let id = $(this).data('id');
                    let lotSelect = $(`.item-lot-select[data-id="${id}"]`);
                    itemsData[id] = {
                        quantity: $(this).val(),
                        // Store Admin sends lot_detail_id to save assignment
                        lot_detail_id: lotSelect.length ? lotSelect.val() : null
                    };
                });
                let url = "{{ route('requisitions.update_items', ':id') }}".replace(':id',
                    currentRequisitionId);
                let btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

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
                    error: function(err) {
                        toastr.error("Failed to update.");
                    },
                    complete: function() {
                        btn.html('<i class="fas fa-save"></i> Save Assignments & Qty').prop(
                            'disabled', false);
                    }
                });
            });

            // 3. Approve
            $('#btn-approve').click(function() {
                let url = "{{ route('requisitions.approve', ':id') }}".replace(':id',
                currentRequisitionId);
                let comments = $('#action-comments').val();
                let actionText = isStoreAdmin ? 'Approve' : 'Process & Complete';

                // Validation for Store Admin: Must assign lots
                if (isStoreAdmin && $('.item-lot-select').filter((_, el) => !$(el).val()).length > 0) {
                    toastr.warning("Please assign a Brand/Spec to all items before approving.");
                    return;
                }

                Swal.fire({
                    title: actionText + '?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, ' + actionText
                }).then((res) => {
                    if (res.isConfirmed) {
                        let form = $('#approve-form');
                        form.attr('action', url);
                        form.html(
                        `@csrf <input type="hidden" name="comments" value="${comments}">`);
                        form.submit();
                    }
                });
            });

            // 4. Reject
            $('#btn-reject').click(function() {
                let url = "{{ route('requisitions.reject', ':id') }}".replace(':id', currentRequisitionId);
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
                        `@csrf <input type="hidden" name="comments" value="${comments}">`);
                        form.submit();
                    }
                });
            });

            $(document).on('change', '.item-lot-select', function() {
                evaluateApproveButtonFromUI();
            });

            function evaluateApproveButtonFromUI() {
                // Only Store Admin needs this check
                if (!isStoreAdmin) return;

                let canApprove = true;
                if ($('.stock-unavailable').length > 0) canApprove = false;
                $('.item-lot-select').each(function() {
                    if (!$(this).val()) {
                        canApprove = false;
                        return false;
                    }
                });
                const btn = $('#btn-approve');
                if (canApprove) {
                    btn.prop('disabled', false).attr('title', 'Ready to approve');
                } else {
                    btn.prop('disabled', true).attr('title', 'Please assign stock to all items first');
                }
            }
        });
    </script>
@endpush
