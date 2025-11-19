@extends('layouts.app')

@section('styles')
    <style>
        /* Timeline CSS */
        .timeline {
            border-left: 2px solid #dee2e6;
            margin-left: 10px;
            padding-left: 20px;
            position: relative;
        }

        .timeline-item {
            margin-bottom: 20px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            width: 12px;
            height: 12px;
            background: #fff;
            border: 2px solid #007bff;
            border-radius: 50%;
            position: absolute;
            left: -27px;
            top: 5px;
        }

        .timeline-item.approved::before {
            border-color: #28a745;
            background: #28a745;
        }

        .timeline-item.rejected::before {
            border-color: #dc3545;
            background: #dc3545;
        }

        .timeline-item.pending::before {
            border-color: #ffc107;
            background: #ffc107;
        }

        .timeline-header {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .timeline-comment {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
            font-size: 0.9rem;
            font-style: italic;
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
                                    {{-- OPEN MODAL BUTTON --}}
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
        <div class="modal-dialog modal-xl" role="document"> {{-- Large Modal --}}
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
                        <div class="col-md-8 border-right">
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
                        <div class="col-md-4">
                            <h6 class="font-weight-bold text-primary mb-3">Approval Timeline</h6>
                            <div id="modal-timeline" class="timeline">
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
                $('#modal-timeline').html('Loading history...');
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
                        res.timeline.forEach(log => {
                            let statusClass = '';
                            if (log.status === 'approved') statusClass = 'approved';
                            if (log.status === 'rejected') statusClass = 'rejected';
                            if (log.status === 'pending') statusClass = 'pending';

                            let commentHtml = log.comments ?
                                `<div class="timeline-comment">"${log.comments}"</div>` :
                                '';

                            timelineHtml += `
                            <div class="timeline-item ${statusClass}">
                                <div class="timeline-header">${log.user} <small>(${log.status})</small></div>
                                <div class="timeline-date">${log.date}</div>
                                ${commentHtml}
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
