@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('stock.current_stock_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('stock.current_stock_title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('menupermission.filter') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Filter: Category --}}
                            <div class="col-md-4 form-group">
                                <label for="category_filter">{{ __('category.title') }}</label>
                                <select class="form-control js-example-basic-single" id="category_filter">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter: Product (Dependent) --}}
                            <div class="col-md-4 form-group">
                                <label for="product_filter">{{ __('product.title') }}</label>
                                <select class="form-control js-example-basic-single" id="product_filter" disabled>
                                    <option value="">Select Category First</option>
                                </select>
                            </div>

                            {{-- Filter: Purchase Type --}}
                            <div class="col-md-4 form-group">
                                <label for="purchase_type_filter">{{ __('lot.purchase_type') }}</label>
                                <select class="form-control js-example-basic-single" id="purchase_type_filter">
                                    <option value="">All Purchase Types</option>
                                    @foreach ($purchaseTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary float-right" type="button" id="filter-btn">
                            <i class="fas fa-search"></i> {{ __('buttons.search') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Report Results</h3>
                    </div>
                    <div class="card-body" id="stock-list-container">
                        {{-- Partial view loaded here --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // 1. Load Stock Logic
            function loadStock() {
                // UPDATED ROUTE NAME
                $.ajax({
                    url: "{{ route('reports.stock.current') }}",
                    type: 'GET',
                    data: {
                        category_id: $('#category_filter').val(),
                        product_id: $('#product_filter').val(),
                        purchase_type_id: $('#purchase_type_filter').val()
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(response) {
                        $('#wait').hide();
                        $('#stock-list-container').html(response);

                        // Initialize DataTable with Excel Button
                        $('#current-stock-table').DataTable({
                            dom: 'Bfrtip',
                            buttons: [
                                'copy', 'csv', 'excel', 'pdf', 'print'
                            ],
                            "columnDefs": [{
                                    "width": "10%",
                                    "targets": 0,
                                    "className": "text-center"
                                },
                                {
                                    "width": "20%",
                                    "targets": 1
                                },
                                {
                                    "width": "30%",
                                    "targets": 2
                                },
                                {
                                    "width": "25%",
                                    "targets": 3
                                },
                                {
                                    "width": "15%",
                                    "targets": 4,
                                    "className": "text-center font-weight-bold"
                                }
                            ]
                        });
                    },
                    error: function(xhr) {
                        $('#wait').hide();
                        toastr.error('Failed to load stock report.');
                    }
                });

            }

            // Initial load
            loadStock();

            // Search Button Click
            $('#filter-btn').on('click', function() {
                loadStock();
            });

            // 2. Dependent Dropdown (Category -> Product)
            $('#category_filter').on('change', function() {
                const categoryId = $(this).val();
                const productSelect = $('#product_filter');
                const urlTemplate = "{{ route('products.by_category', ['categoryId' => ':id']) }}";

                productSelect.prop('disabled', true).html('<option value="">Loading...</option>');

                if (!categoryId) {
                    productSelect.html('<option value="">Select Category First</option>');
                    return;
                }

                const finalUrl = urlTemplate.replace(':id', categoryId);

                $.ajax({
                    url: finalUrl,
                    type: 'GET',
                    success: function(products) {
                        let options = '<option value="">All Products</option>';
                        products.forEach(function(product) {
                            options +=
                                `<option value="${product.id}">${product.name}</option>`;
                        });
                        productSelect.html(options).prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load products.');
                        productSelect.html('<option value="">Error</option>');
                    }
                });
            });
        });
    </script>
@endpush
