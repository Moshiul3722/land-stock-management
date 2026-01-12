@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('product.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <a href={{ route('products.index') }}>{{ __('breadcrumb.list') }}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <!-- Filters -->
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">{{ __('menupermission.filter') }}</h3>
                            <div>
                                <a href="{{ route('products.create') }}" class="btn btn-sm btn-success"
                                    title="{{ __('buttons.create') }}">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Category Filter --}}
                            <div class="col-md-4 form-group">
                                <label for="category_filter">{{ __('category.title') }}</label>
                                <select class="form-control js-example-basic-single custom-select" id="category_filter" name="category_filter">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Sub Category Filter (Dependent) --}}
                            <div class="col-md-4 form-group">
                                <label for="sub_category_filter">Sub Category</label>
                                <select class="form-control js-example-basic-single custom-select" id="sub_category_filter" name="sub_category_filter" disabled>
                                    <option value="">Select Category First</option>
                                </select>
                            </div>

                            {{-- Search Button --}}
                            <div class="col-md-4 form-group d-flex align-items-end">
                                <button class="btn btn-primary w-100" type="button" id="filter-btn">
                                    <i class="fas fa-search"></i> {{ __('buttons.search') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- Product List Container -->
            <div class="card card-primary card-outline" id="product-list-container">
                {{-- Data loaded via AJAX --}}
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.js-example-basic-single').select2();

            // 1. Function to load products
            function loadProducts(categoryId = '', subCategoryId = '') {
                const url = "{{ route('products.index') }}";
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        category_id: categoryId,
                        sub_category_id: subCategoryId
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(response) {
                        $('#wait').hide();
                        $('#product-list-container').html(response);
                        $('#products-table').DataTable({
                        });
                    },
                    error: function(xhr) {
                        $('#wait').hide();
                        toastr.error('Failed to load products.');
                    }
                });
            }

            // Initial load (All products)
            loadProducts();

            // 2. Search Button Click
            $('#filter-btn').on('click', function() {
                var categoryId = $('#category_filter').val();
                var subCategoryId = $('#sub_category_filter').val();
                loadProducts(categoryId, subCategoryId);
            });

            // 3. Dependent Dropdown Logic (Load Sub Categories)
            $('#category_filter').on('change', function() {
                var categoryId = $(this).val();
                var subCategorySelect = $('#sub_category_filter');

                // Reset
                subCategorySelect.html('<option value="">All Sub Categories</option>').prop('disabled', true);

                if (categoryId) {
                    // Uses the route we defined earlier: get.sub_categories
                    var url = "{{ route('get.sub_categories', ':id') }}";
                    url = url.replace(':id', categoryId);

                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    subCategorySelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                subCategorySelect.prop('disabled', false);
                            }
                        },
                        error: function() {
                            console.log('Error fetching sub-categories');
                        }
                    });
                } else {
                     subCategorySelect.html('<option value="">Select Category First</option>');
                }
            });

            // Delete functionality
            $('#product-list-container').on('click', '.delete-btn', function() {
                var productId = $(this).data('id');
                const deleteUrlTemplate = "{{ route('products.destroy', ['product' => ':id']) }}";
                const finalUrl = deleteUrlTemplate.replace(':id', productId);

                Swal.fire({
                    title: "{{ __('sweetalert.confirmation') }}",
                    text: "{{ __('sweetalert.delete_revert_text') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: "{{ __('sweetalert.cancel') }}",
                    confirmButtonText: "{{ __('sweetalert.delete_notice') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: finalUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            beforeSend: function() {
                                $('#wait').show();
                            },
                            success: function(response) {
                                $('#wait').hide();
                                toastr.success("{{ __('product.toastr_delete') }}");
                                // Reload with current filters
                                $('#filter-btn').click();
                            },
                            error: function(xhr) {
                                $('#wait').hide();
                                toastr.error("{{ __('sweetalert.toastr_error') }}");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush