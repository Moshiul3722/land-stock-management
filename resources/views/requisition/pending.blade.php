@extends('layouts.app')

@section('styles')
    <style>
        /* Clean Vertical Timeline */
        .timeline-container {
            position: relative;
            padding: 20px 0;
        }

        /* The Vertical Line */
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 15px;
            /* Align with dots */
            width: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .timeline-entry {
            position: relative;
            margin-bottom: 25px;
            padding-left: 45px;
            /* Space for dot */
            z-index: 1;
        }

        /* The Dot (0) */
        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            text-align: center;
            line-height: 32px;
            color: #fff;
            font-size: 14px;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Colors */
        .icon-primary {
            background-color: #007bff;
        }

        .icon-success {
            background-color: #28a745;
        }

        .icon-danger {
            background-color: #dc3545;
        }

        .icon-warning {
            background-color: #ffc107;
            color: #1f2d3d;
        }

        /* Content Styling */
        .timeline-content {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 10px 15px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .timeline-user {
            font-weight: bold;
            font-size: 1rem;
            color: #333;
        }

        .timeline-role {
            font-size: 0.85rem;
            color: #6c757d;
            font-style: italic;
        }

        .timeline-date {
            font-size: 0.8rem;
            color: #adb5bd;
        }

        .timeline-comment {
            margin-top: 8px;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            padding: 8px 12px;
            font-size: 0.9rem;
            color: #555;
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
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="actionModalLabel">Requisition Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        onclick="$('#actionModal').modal('hide')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- LEFT COLUMN: Details & Items --}}
                        <div class="col-md-7 border-right">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Requested By:</strong> <span id="modal-requestor"></span>
                                </div>
                                <div class="col-md-6 text-right">
                                    <strong>Date:</strong> <span id="modal-date"></span>
                                </div>
                            </div>

                            <h6 class="font-weight-bold text-primary">Requested Items</h6>
                            <form id="items-form">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th width="150">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-items-body">
                                        {{-- Items loaded via AJAX --}}
                                    </tbody>
                                </table>
                                <div class="text-right mb-3">
                                    <button type="button" id="btn-update-qty" class="btn btn-warning btn-sm">
                                        <i class="fas fa-save"></i> Save Quantity Changes
                                    </button>
                                </div>
                            </form>

                            <div class="form-group mt-4">
                                <label for="action-comments">Your Remarks / Comments (Required for Rejection)</label>
                                <textarea class="form-control" id="action-comments" rows="3" placeholder="Enter comments here..."></textarea>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: Timeline --}}
                        <div class="col-md-5">
                            <h6 class="font-weight-bold text-primary mb-3">Approval Timeline</h6>
                            <div id="modal-timeline" class="timeline-container">
                                {{-- Timeline loaded via AJAX --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#actionModal').modal('hide')">Close</button>
                    <button type="button" class="btn btn-danger" id="btn-reject">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success" id="btn-approve">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Forms for Submission --}}
    <form id="approve-form" method="POST" style="display:none;">@csrf</form>
    <form id="reject-form" method="POST" style="display:none;">@csrf</form>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('#pending-req-table').DataTable();

            let currentRequisitionId = null;

            // 1. OPEN MODAL & FETCH DATA
            $('.view-action-btn').click(function() {
                let url = $(this).data('url');
                currentRequisitionId = $(this).data('id');

                // Clear previous data
                $('#modal-requestor').text('Loading...');
                $('#modal-date').text('');
                $('#modal-items-body').html(
                    '<tr><td colspan="3" class="text-center">Loading items...</td></tr>');
                $('#modal-timeline').html(
                    '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>'
                    );
                $('#action-comments').val('');

                // Show Modal
                $('#actionModal').modal('show');

                // Ajax Call
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        // Populate Header
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(res.formatted_date);

                        // Populate Items
                        let itemsHtml = '';
                        res.requisition.items.forEach(item => {
                            itemsHtml += `
                            <tr>
                                <td>${item.product.name}</td>
                                <td>${item.product.category.name}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-qty-input" 
                                           data-id="${item.id}" value="${item.quantity}" min="1">
                                </td>
                            </tr>
                        `;
                        });
                        $('#modal-items-body').html(itemsHtml);

                        // Populate Timeline
                        let timelineHtml = '';
                        res.timeline.forEach(item => {
                            let commentHtml = item.comments ?
                                `<div class="timeline-comment">"${item.comments}"</div>` :
                                '';

                            timelineHtml += `
                            <div class="timeline-entry">
                                <div class="timeline-icon icon-${item.color}">
                                    <i class="fas ${item.icon}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-user">${item.user}</span>, Date & Time - 
                                        <span class="timeline-date">${item.date}</span>
                                    </div>
                                    <div class="timeline-role">${item.role} - ${item.title}</div>
                                    ${commentHtml}
                                </div>
                            </div>
                        `;
                        });
                        $('#modal-timeline').html(timelineHtml);
                    },
                    error: function() {
                        toastr.error("Failed to load details");
                    }
                });
            });

            // 2. HANDLE UPDATE QUANTITY
            $('#btn-update-qty').click(function() {
                let items = {};
                $('.item-qty-input').each(function() {
                    items[$(this).data('id')] = $(this).val();
                });

                let url = "{{ route('requisitions.update_items', ':id') }}".replace(':id',
                    currentRequisitionId);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        items: items
                    },
                    success: function(res) {
                        toastr.success(res.message);
                    },
                    error: function() {
                        toastr.error("Failed to update quantities");
                    }
                });
            });

            // 3. HANDLE APPROVE
            $('#btn-approve').click(function() {
                let url = "{{ route('requisitions.approve', ':id') }}".replace(':id',
                currentRequisitionId);
                let comments = $('#action-comments').val();

                Swal.fire({
                    title: 'Approve Requisition?',
                    text: "Are you sure you want to approve this?",
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let form = $('#approve-form');
                        form.attr('action', url);
                        // Add comments input dynamically
                        form.html(
                        `@csrf <input type="hidden" name="comments" value="${comments}">`);
                        form.submit();
                    }
                });
            });

            // 4. HANDLE REJECT
            $('#btn-reject').click(function() {
                let url = "{{ route('requisitions.reject', ':id') }}".replace(':id', currentRequisitionId);
                let comments = $('#action-comments').val();

                if (!comments) {
                    toastr.error("Please enter comments to reject.");
                    $('#action-comments').focus();
                    return;
                }

                Swal.fire({
                    title: 'Reject Requisition?',
                    text: "This will stop the workflow.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, Reject'
                }).then((result) => {
                    if (result.isConfirmed) {
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