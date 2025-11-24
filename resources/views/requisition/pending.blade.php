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
                        <li class="breadcrumb-item active">{{ __('requisition.pending_title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ __('requisition.pending_list') }}</h3>
            </div>
            <div class="card-body">
                <table id="pending-req-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('lot.table_sl') }}</th>
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
                                <button type="button" id="btn-update-qty" class="btn btn-warning btn-sm shadow-sm">
                                    <i class="fas fa-save"></i> Update Quantities
                                </button>
                            </div>

                            <form id="items-form">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th width="120">Req. Qty</th>
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
                    <button type="button" class="btn btn-danger" id="btn-reject"><i class="fas fa-times"></i>
                        Reject</button>
                    <button type="button" class="btn btn-success" id="btn-approve"><i class="fas fa-check"></i>
                        Approve</button>
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
                            // Determine color for stock status
                            // Check if item.current_stock is defined, default to 0 if not
                            let currentStock = item.current_stock !== undefined ? item
                                .current_stock : 'N/A';
                            let stockClass = 'text-dark';

                            if (currentStock !== 'N/A') {
                                stockClass = currentStock >= item.quantity ?
                                    'text-success font-weight-bold' :
                                    'text-danger font-weight-bold';
                            }

                            itemsHtml += `
                            <tr>
                                <td>${item.product.name}</td>
                                <td>${item.product.category.name}</td>
                                <td class="text-center ${stockClass}">
                                    ${currentStock}
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control form-control-sm item-qty-input text-center" 
                                           data-id="${item.id}" 
                                           value="${item.quantity}" 
                                           min="1">
                                </td>
                            </tr>`;
                        });
                        $('#modal-items-body').html(itemsHtml);

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

            // 2. Update Quantity Logic
            $('#btn-update-qty').click(function() {
                // Gather data from inputs
                let itemsData = {};
                $('.item-qty-input').each(function() {
                    itemsData[$(this).data('id')] = $(this).val();
                });

                let url = "{{ route('requisitions.update_items', ':id') }}".replace(':id',
                    currentRequisitionId);

                // Show simple loading state
                let btn = $(this);
                let originalText = btn.html();
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
                        // Optional: Refresh the modal data to update stock warnings logic if implemented frontend side
                    },
                    error: function(err) {
                        toastr.error("Failed to update quantities");
                    },
                    complete: function() {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // 3. Approve Logic
            $('#btn-approve').click(function() {
                let url = "{{ route('requisitions.approve', ':id') }}".replace(':id',
                currentRequisitionId);
                let comments = $('#action-comments').val();
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
        });
    </script>
@endpush
