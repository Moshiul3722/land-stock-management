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
                    <form>
                        @csrf
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="role" class="col-sm-2 col-form-label">{{ __('category.title') }}
                                    <span style="color: red;">*</span></label>
                                <div class="col-sm-6">
                                    <select class="js-example-basic-single custom-select form-control-border"
                                        id="category_filter" name="category_filter">
                                        <option value="0">All</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <button class="btn btn btn-primary" type="button" id="filter-btn" name="filter-btn"><i
                                            class="fas fa-search"></i>
                                        {{ __('buttons.search') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- Filters -->
            <div class="card card-primary" id="product-list-container">

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Function to load products
            function loadProducts(categoryId = '') {
                const url = "{{ route('products.index') }}";
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        category_id: categoryId
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(response) {
                        $('#wait').hide();
                        $('#product-list-container').html(response);
                        $('#products-table').DataTable({
                            "columnDefs": [{
                                    "width": "20%",
                                    "targets": [2, 3]
                                },
                                {
                                    "width": "10%",
                                    "targets": 0,
                                    "className": "text-center"
                                },
                                {
                                    "width": "30%",
                                    "targets": 1
                                },
                                {
                                    "width": "20%",
                                    "targets": 4,
                                    "className": "text-center"
                                }
                            ]
                        });
                    },
                    error: function(xhr) {
                        $('#wait').hide();
                        toastr.error('Failed to load products.');
                    }
                });
            }

            // Initial load
            loadProducts();

            // Filter button click event
            $('#filter-btn').on('click', function() {
                var categoryId = $('#category_filter').val();
                loadProducts(categoryId);
            });

            // Delete functionality using event delegation
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
                                toastr.success(
                                    "{{ __('product.toastr_delete') }}");
                                // Reload the table with the current filter
                                $('#filter-btn').click();
                            },
                            error: function(xhr) {
                                $('#wait').hide();
                                toastr.error(
                                    "{{ __('sweetalert.toastr_delete') }}"
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
