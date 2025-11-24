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
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ __('product.filter') }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Row 1: Product Filters --}}
                    <div class="col-md-3 form-group">
                        <label>{{ __('product.cateogory') }}</label>
                        <select class="form-control" id="category_filter">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>{{ __('product.product_name') }}</label>
                        <select class="form-control" id="product_filter" disabled>
                            <option value="">Select Category First</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>{{ __('product.purchase_type') }}</label>
                        <select class="form-control" id="purchase_type_filter">
                            <option value="">All Types</option>
                            @foreach ($purchaseTypes as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Row 2: Org & Time Filters --}}
                    <div class="col-md-3 form-group">
                        <label>{{ __('product.department') }}</label>
                        <select class="form-control" id="department_filter">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>{{ __('requisition.start_date') }}</label>
                        <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-01') }}">
                        {{-- Default to 1st of month --}}
                    </div>
                    <div class="col-md-3 form-group">
                        <label>{{ __('requisition.end_date') }}</label>
                        <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="filter-btn"><i
                                class="fas fa-search"></i>{{ __('product.generate_report') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">{{ __('product.report_data') }}</h3>
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
                            dom: "<'row'<'col-md-6'B><'col-md-6 text-right'f>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                            buttons: [{
                                    // --- EXCEL CONFIGURATION ---
                                    extend: 'excelHtml5',
                                    text: '<i class="fas fa-file-excel"></i> Excel',
                                    title: 'Ministry of Land - Inventory Management System',
                                    messageTop: 'Current Stock Report | Generated on: ' +
                                        new Date().toLocaleString(), // Subtitle
                                    exportOptions: {
                                        columns: ':visible' // Only export visible columns
                                    },
                                    customize: function(xlsx) {
                                        // Optional: Make the title row bold and larger
                                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                        $('row:first c', sheet).attr('s',
                                            '51'); // Apply a preset bold style
                                    }
                                },
                                {
                                    // --- PRINT CONFIGURATION (WITH LOGO) ---
                                    extend: 'print',
                                    text: '<i class="fas fa-print"></i> Print',
                                    title: '', // Remove default title to use our custom HTML below
                                    messageTop: `
                                        <div style="text-align: center; margin-bottom: 20px;">
                                            <img src="{{ asset('theme/assets/img/gov.png') }}" style="height: 80px; margin-bottom: 10px;">
                                            <h2 style="margin:0;">Ministry of Land</h2>
                                            <h4 style="margin:5px 0;">Inventory Management System</h4>
                                            <p style="margin:0;">Requisition Report | Generated: ${new Date().toLocaleDateString()}</p>
                                        </div>
                                    `,
                                    customize: function(win) {
                                        $(win.document.body).css('font-size', '10pt');
                                        $(win.document.body).find('table')
                                            .addClass('compact')
                                            .css('font-size', 'inherit');
                                    }
                                },
                            ],
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
