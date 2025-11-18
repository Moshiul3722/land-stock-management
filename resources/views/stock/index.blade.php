@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('stock.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.list') }}</li>
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
                        <div class="form-group row">
                            <label for="category_filter" class="col-sm-2 col-form-label">{{ __('category.title') }}</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="category_filter" name="category_filter">
                                    <option value="">All</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- <label for="purchase_type_filter"
                                class="col-sm-2 col-form-label">{{ __('lot.purchase_type') }}</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="purchase_type_filter" name="purchase_type_filter">
                                    <option value="">All</option>
                                    @foreach ($purchaseTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary float-end" type="button" id="filter-btn">
                            <i class="fas fa-search"></i> {{ __('buttons.search') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('stock.list_title') }}</h3>
                    </div>
                    <div class="card-body" id="stock-list-container">
                        {{-- Partial view will be loaded here --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Function to load stock
            function loadStock(categoryId = '', purchaseTypeId = '') {
                const url = "{{ route('stock.index') }}";
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        category_id: categoryId,
                        purchase_type_id: purchaseTypeId
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(response) {
                        $('#wait').hide();
                        $('#stock-list-container').html(response);
                        $('#stock-table').DataTable({
                            "columnDefs": [
                                // SL (Col 0)
                                {
                                    "width": "10%",
                                    "targets": 0,
                                    "className": "text-center"
                                },
                                // Category (Col 1)
                                {
                                    "width": "30%",
                                    "targets": 1
                                },
                                // Product (Col 2)
                                {
                                    "width": "30%",
                                    "targets": 2
                                },
                                // Quantity (Col 3)
                                {
                                    "width": "15%",
                                    "targets": 3,
                                    "className": "text-center"
                                },
                                // Action (Col 4)
                                {
                                    "width": "15%",
                                    "targets": 4,
                                    "className": "text-center"
                                }
                            ]
                        });
                    },
                    error: function(xhr) {
                        $('#wait').hide();
                        toastr.error('Failed to load stock list.');
                    }
                });
            }

            // Initial load
            loadStock();

            // Filter button click event
            $('#filter-btn').on('click', function() {
                var categoryId = $('#category_filter').val();
                var purchaseTypeId = $('#purchase_type_filter').val();
                loadStock(categoryId, purchaseTypeId);
            });
        });
    </script>
@endpush
