@extends('layouts.app')
@section('page_css')
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
                    <h1>{{ __('requisition.my_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('requisition.my_title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        {{-- Filter Card --}}
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ __('menupermission.filter') }}</h3>
            </div>
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
                    <div class="col-md-3">
                        <label>{{ __('requisition.start_date') }}</label>
                        <input type="date" class="form-control" id="start_date">
                    </div>
                    <div class="col-md-3">
                        <label>{{ __('requisition.end_date') }}</label>
                        <input type="date" class="form-control" id="end_date">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button id="filter-btn" class="btn btn-primary w-100"><i class="fas fa-search"></i>
                            {{ __('buttons.search') }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- List Card --}}
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ __('requisition.list_title') }}</h3>
                <a href="{{ route('requisitions.create') }}" class="btn btn-sm btn-success ms-auto"><i
                        class="fas fa-plus"></i></a>
            </div>
            <div class="card-body" id="requisition-list-container">
                {{-- Partial view loads here --}}
            </div>
        </div>
    </div>

    {{-- DETAILS MODAL --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Requisition Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        onclick="$('#detailsModal').modal('hide')">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Left: Info & Items --}}
                        <div class="col-md-7 border-right">
                            <div class="mb-3">
                                <strong>Requestor:</strong> <span id="modal-requestor"></span><br>
                                <strong>Date:</strong> <span id="modal-date"></span>
                            </div>
                            <h6 class="font-weight-bold text-info">Items</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-items-body"></tbody>
                            </table>
                        </div>
                        {{-- Right: Timeline --}}
                        <div class="col-md-5">
                            <h6 class="font-weight-bold text-info mb-3">Status History</h6>
                            <div id="modal-timeline" class="timeline"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#detailsModal').modal('hide')">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            function loadRequisitions() {
                $.ajax({
                    url: "{{ route('requisitions.index') }}",
                    type: "GET",
                    data: {
                        status: $('#status_filter').val(),
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(res) {
                        $('#wait').hide();
                        $('#requisition-list-container').html(res);
                        $('#my-req-table').DataTable();
                    },
                    error: function() {
                        $('#wait').hide();
                        toastr.error('Error loading data');
                    }
                });
            }

            loadRequisitions(); // Initial load

            $('#filter-btn').click(function() {
                loadRequisitions();
            });

            // --- VIEW (MODAL) LOGIC ---
            $(document).on('click', '.view-req-btn', function() {
                let url = $(this).data('url');

                // Reset Modal
                $('#modal-requestor').text('Loading...');
                $('#modal-items-body').html('');
                $('#modal-timeline').html('');
                $('#detailsModal').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        $('#modal-requestor').text(res.requestor);
                        $('#modal-date').text(res.formatted_date);

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
                            let statusClass = log.status === 'approved' ? 'approved' : (
                                log.status === 'rejected' ? 'rejected' : 'pending');
                            let commentHtml = log.comments ?
                                `<div class="timeline-comment">"${log.comments}"</div>` :
                                '';
                            timelineHtml += `
                            <div class="timeline-item ${statusClass}">
                                <div class="timeline-header">${log.user} <small>(${log.status})</small></div>
                                <div class="timeline-date">${log.date}</div>
                                ${commentHtml}
                            </div>`;
                        });
                        $('#modal-timeline').html(timelineHtml);
                    }
                });
            });

            // --- DELETE LOGIC ---
            $(document).on('click', '.delete-req-btn', function() {
                let id = $(this).data('id');
                let url = "{{ route('requisitions.destroy', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
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
                                loadRequisitions(); // Refresh list
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON.message ||
                                    'Error deleting');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
