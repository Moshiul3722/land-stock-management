@extends('layouts.app')

@push('page_css')
    <style>
        /* macOS Golden Gate 27-inspired UI styles */
        body {
            background-color: #f5f6f8;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        .table th {
            border-top: none;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge {
            padding: 0.5em 0.8em;
            border-radius: 6px;
            font-weight: 500;
        }
    </style>
@endpush

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">{{ __('requisition.my_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.list') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        {{-- Filter Card --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="text-muted small font-weight-bold">{{ __('lot.table_status') }}</label>
                        <select class="form-control" id="status_filter">
                            <option value="">All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small font-weight-bold">{{ __('requisition.start_date') }}</label>
                        <input type="date" class="form-control" id="start_date">
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted small font-weight-bold">{{ __('requisition.end_date') }}</label>
                        <input type="date" class="form-control" id="end_date">
                    </div>
                    <div class="col-md-3">
                        <button id="filter-btn" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> {{ __('buttons.search') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- List Card --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">{{ __('requisition.list_title') }}</h3>
                <button type="button" class="btn btn-success btn-sm ms-auto" id="open-create-modal">
                    <i class="fas fa-plus"></i> New Requisition
                </button>
            </div>
            <div class="card-body" id="requisition-list-container">
                {{-- Partial view loads here --}}
            </div>
        </div>
    </div>

    {{-- GENERAL PURPOSE MODAL --}}
    <div class="modal fade" id="genericModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modalTitle">Modal Title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="modalContentBody">
                    {{-- Content loaded dynamically --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_js')
<script>
    $(document).ready(function() {
        function loadRequisitions() {
            let status = $('#status_filter').val();
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();

            $.ajax({
                url: "{{ route('requisitions.index') }}",
                type: "GET",
                data: { status, start_date, end_date },
                success: function(response) {
                    $('#requisition-list-container').html(response);
                }
            });
        }

        loadRequisitions();

        $('#filter-btn').click(function() {
            loadRequisitions();
        });

        // Open Create Modal
        $('#open-create-modal').click(function() {
            $('#modalTitle').text('New Requisition');
            $.get("{{ route('requisitions.create_modal') }}", function(html) {
                $('#modalContentBody').html(html);
                $('#genericModal').modal('show');
            });
        });

        // Open Edit Modal
        $(document).on('click', '.edit-req-btn', function() {
            let url = $(this).data('url');
            $('#modalTitle').text('Edit Requisition');
            $.get(url, function(html) {
                $('#modalContentBody').html(html);
                $('#genericModal').modal('show');
            });
        });

        // Delete Requisition
        $(document).on('click', '.delete-req-btn', function() {
            let id = $(this).data('id');
            if (confirm('Are you sure you want to delete this requisition?')) {
                $.ajax({
                    url: '/requisitions/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        loadRequisitions();
                        alert('Deleted successfully.');
                    }
                });
            }
        });
    });
</script>
@endpush
