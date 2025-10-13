@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">Filter Products</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="category_filter">Product Category</label>
                        <select id="category_filter" class="form-control">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button id="filter-btn" class="btn btn-primary float-end">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                 <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Products</span>
                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                </div>
                <div class="card-body">
                    <div id="product-list-container">
                        {{-- The partial view will be loaded here via AJAX --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Function to load products
        function loadProducts(categoryId = '') {
            const url = "{{ route('products.index') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: { category_id: categoryId },
                success: function(response) {
                    $('#product-list-container').html(response);
                    $('#products-table').DataTable();
                },
                error: function(xhr) {
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
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: finalUrl,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            // Reload the table with the current filter
                            $('#filter-btn').click();
                        },
                        error: function(xhr) {
                            toastr.error('An error occurred.');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
