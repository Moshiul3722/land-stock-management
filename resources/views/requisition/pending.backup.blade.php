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
            /* Center line through numbers */
            width: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .timeline-entry {
            position: relative;
            margin-bottom: 25px;
            padding-left: 50px;
            /* Space for number */
            z-index: 1;
        }

        /* Numbered Circle */
        .timeline-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            /* Center text vertically */
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

        .bg-warning {
            background-color: #ffc107 !important;
            color: #333 !important;
        }

        /* Content Box */
        .timeline-content {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px 15px;
            position: relative;
        }

        /* Little arrow pointing to number */
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
            /* Light grey background */
            border-left: 4px solid #17a2b8;
            /* Info color accent */
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
    </style>
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('requisition.pending_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.list') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ __('requisition.pending_title') }}</h3>
            </div>
            <div class="card-body">
                <table id="pending-req-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('lot.table_sl') }}</th>
                            <th>{{ __('requisition.requestor_name') }}</th>
                            <th>{{ __('requisition.requisition_date') }}</th>
                            <th>{{ __('requisition.items_count') }}</th>
                            <th>{{ __('commontable.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingTasks as $task)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
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
                                <h6 class="font-weight-bold text-primary mb-0">Items & Stock Check</h6>
                                {{--
                                    Only show "Update Quantities" button if user is NOT a Store Keeper (Role ID 4).
                                --}}
                                @if (Auth::user()->role_id != 4)
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
                                            <th>Category</th>
                                            <th width="35%">Assigned Lot (Brand/Spec)</th>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#actionModal').modal('hide')">Close</button>
                    @if (Auth::user()->role_id != 4)
                        <button type="button" class="btn btn-danger" id="btn-reject"><i class="fas fa-times"></i>
                            Reject</button>
                    @endif
                    @if (Auth::user()->role_id != 4)
                        <button type="button" class="btn btn-success" id="btn-approve"><i class="fas fa-check"></i>
                            Approve</button>
                    @else
                        <button type="button" class="btn btn-success" id="btn-approve"><i class="fas fa-check"></i>
                            Acknowledge</button>
                    @endif

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
        $(document).ready(function() {
            $('#pending-req-table').DataTable();
            let currentRequisitionId = null;

            // --- DEFINING ROLES ---
            const userRoleId = {{ Auth::user()->role_id }};
            const isStoreAdmin = (userRoleId == 3);
            const isStoreKeeper = (userRoleId == 4);

            // 1. View & Load Data
            $('.view-action-btn').click(function() {
                let url = $(this).data('url');
                currentRequisitionId = $(this).data('id');

                $('#actionModal').modal('show');
                $('#modal-timeline').html('<div class="text-center p-3">Loading...</div>');

                // Reset items table
                $('#modal-items-body').html(
                    '<tr><td colspan="4" class="text-center">Loading items...</td></tr>');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(res.formatted_date);

                        // Populate Items with Stock Info
                        let itemsHtml = '';
                        res.requisition.items.forEach(item => {

                            // --- Logic for Lot Assignment Dropdown ---
                            let assignmentHtml =
                                '<span class="text-muted small font-italic">Pending Assignment</span>';


                            if (isStoreAdmin) {
                                // Store Admin: Can EDIT/ASSIGN Lots
                                let options =
                                    '<option value="">-- Select Brand/Spec --</option>';

                                if (item.available_lots && item.available_lots.length >
                                    0) {
                                    item.available_lots.forEach(lot => {
                                        let selected = (item.lot_id == lot.id) ?
                                            'selected' : '';
                                        let label =
                                            `${lot.brand || 'N/A'} - ${lot.specification || 'N/A'} (Qty: ${lot.quantity})`;
                                        options +=
                                            `<option value="${lot.id}" ${selected}>${label}</option>`;
                                    });
                                    assignmentHtml =
                                        `<select class="form-control form-control-sm item-lot-select" data-id="${item.id}">${options}</select>`;
                                } else {
                                    assignmentHtml =
                                        '<span class="text-danger small font-weight-bold">Stock Unavailable</span>';
                                }
                            } else if (item.lot_id) {
                                // Store Keeper / Others: READ ONLY assignment
                                // We need to find the name of the assigned lot from the available list or pre-loaded data
                                let assignedLotLabel = "Assigned";
                                if (item.available_lots) {
                                    let foundLot = item.available_lots.find(l => l.id ==
                                        item.lot_id);
                                    if (foundLot) {
                                        assignedLotLabel =
                                            `${foundLot.brand || 'N/A'} - ${foundLot.specification || 'N/A'}`;
                                    }
                                }
                                assignmentHtml =
                                    `<span class="text-success small font-weight-bold"><i class="fas fa-check"></i> ${assignedLotLabel}</span>`;
                            } else {
                                // Store Keeper viewing unassigned item (Should not happen if flow is correct)
                                assignmentHtml =
                                    '<span class="text-danger small font-weight-bold">Not Assigned</span>';
                            }

                            // Store Admin can edit Quantity, Store Keeper CANNOT.
                            let disabledAttr = isStoreAdmin ? '' : 'disabled';
                            let inputClass = isStoreAdmin ?
                                'form-control form-control-sm item-qty-input text-center' :
                                'form-control-plaintext text-center font-weight-bold';


                            itemsHtml += `
                            <tr>
                                <td>${item.product.name}</td>
                                <td>${item.product.category.name}</td>
                                <td>${assignmentHtml}</td>
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

                        evaluateApproveButton(res.requisition.items);

                        // Populate Timeline (Your existing timeline logic)
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

            // 2. Update Quantity & Assignment Logic (Only active for Store Admin)
            $('#btn-update-qty').click(function() {
                let itemsData = {};

                // Loop through inputs to gather Quantity AND Lot ID
                $('.item-qty-input').each(function() {
                    let id = $(this).data('id');
                    let lotSelect = $(`.item-lot-select[data-id="${id}"]`);

                    itemsData[id] = {
                        quantity: $(this).val(),
                        lot_id: lotSelect.length ? lotSelect.val() :
                            null // Capture selected Lot ID
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
                        items: itemsData // Sending structured data
                    },
                    success: function(res) {
                        toastr.success(res.message);
                        evaluateApproveButtonFromUI();
                    },
                    error: function(err) {
                        toastr.error(
                            "Failed to update. Make sure required fields are selected.");
                    },
                    complete: function() {
                        btn.html('<i class="fas fa-save"></i> Save Assignments & Qty').prop(
                            'disabled', false);
                    }
                });
            });

            // 3. Approve Logic
            $('#btn-approve').click(function() {
                let url = "{{ route('requisitions.approve', ':id') }}".replace(':id',
                    currentRequisitionId);
                let comments = $('#action-comments').val();

                let actionText = isStoreKeeper ? 'Process & Complete' : 'Approve';

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

            // 4. Reject Logic
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

            // 5 check if the product lot is assigned before approving (for Store Admin)
            function evaluateApproveButton(items) {
                let hasUnavailable = false;
                let hasUnassigned = false;

                items.forEach(item => {
                    // 1️⃣ Stock unavailable
                    if (!item.available_lots || item.available_lots.length === 0) {
                        hasUnavailable = true;
                    }

                    // 2️⃣ Lot not assigned
                    if (!item.lot_id) {
                        hasUnassigned = true;
                    }
                });

                const approveBtn = $('#btn-approve');

                if (hasUnavailable || hasUnassigned) {
                    // approveBtn.prop('disabled', true).hide();
                    approveBtn
                        .prop('disabled', true)
                        .attr('title', 'All items must have stock and assigned brand/spec')
                        .show();
                } else {
                    approveBtn.prop('disabled', false).show();
                }
            }

            function evaluateApproveButtonFromUI() {
                let hasUnavailable = false;
                let hasUnassigned = false;

                $('#modal-items-body tr').each(function() {
                    // Stock unavailable text
                    if ($(this).find('.text-danger:contains("Stock Unavailable")').length) {
                        hasUnavailable = true;
                    }

                    // Lot dropdown exists but no selection
                    let lotSelect = $(this).find('.item-lot-select');
                    if (lotSelect.length && !lotSelect.val()) {
                        hasUnassigned = true;
                    }

                    // Read-only "Not Assigned" (for non-admin edge cases)
                    if ($(this).find('.text-danger:contains("Not Assigned")').length) {
                        hasUnassigned = true;
                    }
                });

                const approveBtn = $('#btn-approve');

                if (hasUnavailable || hasUnassigned) {
                    approveBtn
                        .prop('disabled', true)
                        .attr('title', 'All items must have stock and assigned brand/spec')
                        .show();
                } else {
                    approveBtn.prop('disabled', false).removeAttr('title').show();
                }
            }
        });
        $(document).on('change', '.item-lot-select', function() {
            evaluateApproveButtonFromUI();
        });
    </script>
@endpush
