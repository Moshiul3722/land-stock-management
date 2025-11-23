@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Consumption Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>Home</a></li>
                        <li class="breadcrumb-item active">Consumption Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Filter Options</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Row 1: Product Filters --}}
                    <div class="col-md-3 form-group">
                        <label>Category</label>
                        <select class="form-control" id="category_filter">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Product</label>
                        <select class="form-control" id="product_filter" disabled>
                            <option value="">Select Category First</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Purchase Type</label>
                        <select class="form-control" id="purchase_type_filter">
                            <option value="">All Types</option>
                            @foreach ($purchaseTypes as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Row 2: Org & Time Filters --}}
                    <div class="col-md-3 form-group">
                        <label>Department</label>
                        <select class="form-control" id="department_filter">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-01') }}">
                        {{-- Default to 1st of month --}}
                    </div>
                    <div class="col-md-3 form-group">
                        <label>End Date</label>
                        <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="filter-btn"><i class="fas fa-search"></i> Generate
                            Report</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Report Data</h3>
            </div>
            <div class="card-body" id="report-container">
                {{-- Partial loads here --}}
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // 1. Dependent Dropdown
            $('#category_filter').on('change', function() {
                const categoryId = $(this).val();
                const productSelect = $('#product_filter');
                const url = "{{ route('products.by_category', ['categoryId' => ':id']) }}".replace(':id',
                    categoryId);

                productSelect.prop('disabled', true).html('<option value="">Loading...</option>');
                if (!categoryId) {
                    productSelect.html('<option value="">Select Category First</option>');
                    return;
                }

                $.get(url, function(products) {
                    let options = '<option value="">All Products</option>';
                    products.forEach(p => options += `<option value="${p.id}">${p.name}</option>`);
                    productSelect.html(options).prop('disabled', false);
                });
            });

            // 2. Load Report
            function loadReport() {
                $.ajax({
                    url: "{{ route('reports.consumption') }}",
                    type: 'GET',
                    data: {
                        category_id: $('#category_filter').val(),
                        product_id: $('#product_filter').val(),
                        purchase_type_id: $('#purchase_type_filter').val(),
                        department_id: $('#department_filter').val(),
                        start_date: $('#start_date').val(),
                        end_date: $('#end_date').val(),
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(res) {
                        $('#wait').hide();
                        $('#report-container').html(res);
                        $('#consumption-table').DataTable({
                            dom: 'Bfrtip',
                            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                            "pageLength": 25
                        });
                    },
                    error: function() {
                        $('#wait').hide();
                        toastr.error('Failed to load report');
                    }
                });
            }

            // Initial Load & Click Event
            // loadReport(); // Optional: Load on page load? Or wait for user?
            $('#filter-btn').click(function() {
                loadReport();
            });
        });
    </script>
@endpush
