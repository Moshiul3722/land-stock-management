@extends('layouts.app')

@push('page_css')
    <style>
        /* Timeline CSS */
        .timeline-container { position: relative; padding: 10px 0; }
        .timeline-container::before { content: ''; position: absolute; top: 15px; bottom: 0; left: 15px; width: 2px; background: #e9ecef; z-index: 0; }
        .timeline-entry { position: relative; margin-bottom: 25px; padding-left: 50px; z-index: 1; }
        .timeline-number { position: absolute; left: 0; top: 0; width: 32px; height: 32px; border-radius: 50%; text-align: center; line-height: 28px; font-weight: bold; font-size: 14px; color: #fff; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15); z-index: 2; }
        
        .bg-primary { background-color: #007bff !important; }
        .bg-success { background-color: #28a745 !important; }
        .bg-danger { background-color: #dc3545 !important; }
        /* Custom Orange for Pending */
        .bg-warning { background-color: #fd7e14 !important; color: #fff !important; } 
        .bg-info    { background-color: #17a2b8 !important; }

        .timeline-content { background: #fff; border: 1px solid #dee2e6; border-radius: 6px; padding: 12px 15px; position: relative; }
        .timeline-content::after { content: ''; position: absolute; top: 10px; left: -6px; width: 10px; height: 10px; background: #fff; border-left: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; transform: rotate(45deg); }
        .timeline-header { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .user-name { font-weight: bold; font-size: 1rem; color: #343a40; }
        .action-date { font-size: 0.8rem; color: #868e96; }
        .role-badge { display: inline-block; padding: 2px 6px; font-size: 0.75rem; border-radius: 4px; background: #f1f3f5; color: #495057; margin-bottom: 6px; }
        .timeline-comment { margin-top: 8px; background-color: #f8f9fa; border-left: 4px solid #17a2b8; padding: 8px 12px; font-size: 0.9rem; color: #495057; border-radius: 0 4px 4px 0; }
    </style>
@endpush

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>{{ __('requisition.my_title') }}</h1></div>
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
        {{-- Filter Card --}}
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">{{ __('menupermission.filter') }}</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ __('lot.table_status') }}</label>
                        <select class="form-control" id="status_filter">
                            <option value="">All</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label>{{ __('requisition.start_date') }}</label><input type="date" class="form-control" id="start_date"></div>
                    <div class="col-md-3"><label>{{ __('requisition.end_date') }}</label><input type="date" class="form-control" id="end_date"></div>
                    <div class="col-md-3 d-flex align-items-end"><button id="filter-btn" class="btn btn-primary w-100"><i class="fas fa-search"></i> {{ __('buttons.search') }}</button></div>
                </div>
            </div>
        </div>

        {{-- List Card --}}
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ __('requisition.list_title') }}</h3>
                <a href="{{ route('requisitions.create') }}" class="btn btn-sm btn-success ms-auto"><i class="fas fa-plus"></i></a>
            </div>
            <div class="card-body" id="requisition-list-container">
                {{-- Partial view loads here --}}
            </div>
        </div>
    </div>

    {{-- DETAILS MODAL --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Requisition Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#detailsModal').modal('hide')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Left: Info & Items --}}
                        <div class="col-md-7 border-right">
                            <div class="mb-3">
                                <strong>Requestor:</strong> <span id="modal-requestor"></span><br>
                                <strong>Date:</strong> <span id="modal-date"></span><br>
                                <strong>Status:</strong> <span id="modal-status-badge" class="badge"></span>
                            </div>
                            <h6 class="font-weight-bold text-info">Items</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr><th>Product</th><th>Category</th><th class="text-center">Qty</th></tr>
                                </thead>
                                <tbody id="modal-items-body"></tbody>
                            </table>
                        </div>

                        {{-- Right: Timeline --}}
                        <div class="col-md-5">
                            <h6 class="font-weight-bold text-info mb-3 pl-2">Approval History</h6>
                            <div class="timeline-container" id="modal-timeline"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    {{-- NEW: Print Slip Button (Visible for Requestor) --}}
                    <a href="#" id="btn-print-slip" target="_blank" class="btn btn-default mr-auto">
                        <i class="fas fa-print"></i> Print Slip
                    </a>

                    {{-- ACKNOWLEDGE FORM --}}
                    <form id="ack-form" method="POST" style="display: none;">
                        @csrf
                        <button type="button" id="btn-acknowledge" class="btn btn-success">
                            <i class="fas fa-check-double"></i> Confirm Receipt
                        </button>
                    </form>

                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#detailsModal').modal('hide')">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // 1. Load List Logic
            function loadRequisitions() {
                $.ajax({
                    url: "{{ route('requisitions.index') }}",
                    type: "GET",
                    data: {
                        status: $('#status_filter').val(),
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                    },
                    beforeSend: function() { $('#wait').show(); },
                    success: function(res) {
                        $('#wait').hide();
                        $('#requisition-list-container').html(res);
                        $('#my-req-table').DataTable();
                    },
                    error: function() { $('#wait').hide(); toastr.error('Error loading data'); }
                });
            }

            loadRequisitions(); 
            $('#filter-btn').click(function() { loadRequisitions(); });

            // 2. VIEW (MODAL) LOGIC
            $(document).on('click', '.view-req-btn', function() {
                let url = $(this).data('url');
                let id = $(this).data('id'); // Get ID directly
                
                // Reset Modal
                $('#modal-requestor').text('Loading...');
                $('#modal-items-body').html('');
                $('#modal-timeline').html('<div class="text-center p-3">Loading history...</div>');
                $('#ack-form').hide(); 
                
                // Set Print Button URL
                let printUrl = "{{ route('requisitions.print', ':id') }}".replace(':id', id);
                
                $('#btn-print-slip').attr('href', printUrl);

                $('#detailsModal').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(res.formatted_date);

                        // Update Status Badge
                        let status = res.requisition.status;
                        let badgeClass = 'bg-secondary';
                        if(status === 'approved') badgeClass = 'bg-primary';
                        if(status === 'delivered') badgeClass = 'bg-info';
                        if(status === 'completed') badgeClass = 'bg-success';
                        if(status === 'rejected') badgeClass = 'bg-danger';
                        if(status === 'pending') badgeClass = 'bg-warning text-dark';
                        
                        $('#modal-status-badge')
                            .removeClass()
                            .addClass('badge ' + badgeClass)
                            .text(status.toUpperCase());
                            
                        // ============================================================
                        // PRINT SLIP VISIBILITY LOGIC
                        // ============================================================
                        // Show button if flag is true OR if status suggests it's already done
                        let canPrint = res.is_print_enabled || 
                                       status === 'delivered' || 
                                       status === 'completed';

                        if (canPrint) {
                            let printUrl = "{{ route('requisitions.print', ':id') }}".replace(':id', res.requisition.id);
                            $('#btn-print-slip').attr('href', printUrl).show();
                        } else {
                            $('#btn-print-slip').hide();
                        }
                        // ============================================================
                        
                        // SHOW ACKNOWLEDGE BUTTON IF DELIVERED
                        if (status === 'delivered') {
                            let ackUrl = "{{ route('requisitions.acknowledge', ':id') }}".replace(':id', res.requisition.id);
                            $('#ack-form').attr('action', ackUrl).show();
                        } else {
                            $('#ack-form').hide();
                        }

                        // Items
                        let itemsHtml = '';
                        res.requisition.items.forEach(item => {
                            itemsHtml +=
                                `<tr><td>${item.product.name}</td><td>${item.product.category.name}</td><td class="text-center">${item.quantity}</td></tr>`;
                        });
                        $('#modal-items-body').html(itemsHtml);

                        // Timeline
                        let timelineHtml = '';
                        res.timeline.forEach(log => {
                            let badgeClass = 'bg-primary';
                            if(log.status_raw === 'approved' || log.status_raw === 'processed') badgeClass = 'bg-primary';
                            if(log.status_raw === 'delivered') badgeClass = 'bg-info';
                            if(log.status_raw === 'completed') badgeClass = 'bg-success';
                            if(log.status_raw === 'rejected') badgeClass = 'bg-danger';
                            if(log.status_raw === 'pending') badgeClass = 'bg-warning';

                            let commentHtml = log.comments ?
                                `<div class="timeline-comment">"${log.comments}"</div>` : '';

                            timelineHtml += `
                                <div class="timeline-entry">
                                    <div class="timeline-number ${badgeClass}">${log.step}</div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <span class="user-name">${log.user}</span>
                                            <span class="action-date">${log.date}</span>
                                        </div>
                                        <span class="role-badge">${log.role}</span> 
                                        <span class="text-${log.color} font-weight-bold small ml-1">(${log.title})</span>
                                        ${commentHtml}
                                    </div>
                                </div>`;
                        });
                        $('#modal-timeline').html(timelineHtml);
                    },
                    error: function() {
                        toastr.error("Failed to fetch details");
                    }
                });
            });

            // 3. ACKNOWLEDGE ACTION
            $('#btn-acknowledge').click(function() {
                Swal.fire({
                    title: 'Confirm Receipt?',
                    text: "I confirm that I have physically received these items.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Received'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#ack-form').submit();
                    }
                });
            });

            // 4. DELETE LOGIC
            $(document).on('click', '.delete-req-btn', function() {
                let id = $(this).data('id');
                let url = "{{ route('requisitions.destroy', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This draft will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                toastr.success(res.message);
                                loadRequisitions(); 
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON.message || 'Error deleting');
                            }
                        });
                    }
                });
            });

            // 5.  Toastr Logic for Success/Error (Bangla Messages)
            @if(session('success'))
                @if(session('new_requisition_id'))
                    toastr.success("আপনার রিকুইজিশন জমা হয়েছে");
                @else
                    toastr.success("{{ session('success') }}");
                @endif
            @endif

            @if(session('error'))
                toastr.error("রিকুইজিশন জমা হয়নি। সমস্যা হয়েছে");
            @endif
        });
    </script>
@endpush