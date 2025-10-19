@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.edit_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href={{ route('lots.index') }}>{{ __('breadcrumb.list') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.edit') }}</li>
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
                        <h3 class="card-title">{{ __('lot.edit_title') }}</h3>
                    </div>
                    <form action="{{ route('lots.update', $lot) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            {{-- Lot Master Information --}}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="lot_number">{{ __('lot.lot_number') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="lot_number" name="lot_number"
                                        value="{{ old('lot_number', $lot->lot_number) }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="entry_date">{{ __('lot.entry_date') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="date" class="form-control" id="entry_date" name="entry_date"
                                        value="{{ old('entry_date', $lot->entry_date->format('Y-m-d')) }}" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="remarks">{{ __('lot.remarks') }}</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks', $lot->remarks) }}</textarea>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mb-3">Lot Details</h5>

                            {{-- Lot Details Table --}}
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('lot.table_category') }}</th>
                                        <th>{{ __('lot.table_product') }}</th>
                                        <th>{{ __('lot.table_quantity') }}</th>
                                        <th>{{ __('lot.table_unit_price') }}</th>
                                        <th><button type="button" id="add-row" class="btn btn-success btn-sm"><i
                                                    class="fa fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody id="lot-details-body">
                                    @foreach ($lot->lotDetails as $index => $detail)
                                        <tr id="R{{ $index }}">
                                            <td>
                                                <select class="form-control category-select" required>
                                                    <option value="">Select Category</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ optional($detail->product)->category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control product-select"
                                                    name="details[{{ $index }}][product_id]"
                                                    data-selected-product-id="{{ $detail->product_id }}" required disabled>
                                                    <option value="">Select Category First</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control"
                                                    name="details[{{ $index }}][quantity]" min="1"
                                                    value="{{ $detail->quantity }}" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control"
                                                    name="details[{{ $index }}][unit_price]" step="0.01"
                                                    min="0" value="{{ $detail->unit_price }}">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                        class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                {{ __('buttons.update') }}</button>
                            <a href="{{ route('lots.index') }}" class="btn btn-secondary"><i class="fas fa-ban"></i>
                                {{ __('buttons.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            let rowIdx = {{ $lot->lotDetails->count() }};

            // ADD NEW ROW
            $('#add-row').on('click', function() {
                $('#lot-details-body').append(`
            <tr id="R${++rowIdx}">
                <td>
                    <select class="form-control category-select" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="form-control product-select" name="details[${rowIdx}][product_id]" required disabled>
                        <option value="">Select Product</option>
                    </select>
                </td>
                <td><input type="number" class="form-control" name="details[${rowIdx}][quantity]" min="1" required></td>
                <td><input type="number" class="form-control" name="details[${rowIdx}][unit_price]" step="0.01" min="0"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
            </tr>
        `);
            });

            // REMOVE ROW
            $('#lot-details-body').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Function to load products for a given category select element
            function loadProductsForCategory(categorySelectElement) {
                const categoryId = $(categorySelectElement).val();
                const productSelect = $(categorySelectElement).closest('tr').find('.product-select');
                const selectedProductId = productSelect.data('selected-product-id') || '';
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
                        let options = '<option value="">Select Product</option>';
                        products.forEach(function(product) {
                            const isSelected = product.id == selectedProductId ? 'selected' :
                            '';
                            options +=
                                `<option value="${product.id}" ${isSelected}>${product.name}</option>`;
                        });
                        productSelect.html(options).prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load products.');
                        productSelect.html('<option value="">Error</option>');
                    }
                });
            }

            // LOAD PRODUCTS ON CATEGORY CHANGE (for any row)
            $('#lot-details-body').on('change', '.category-select', function() {
                // Clear the data attribute so it doesn't pre-select an old product if the category is changed again
                $(this).closest('tr').find('.product-select').data('selected-product-id', '');
                loadProductsForCategory(this);
            });

            // INITIAL LOAD FOR EXISTING ROWS
            $('.category-select').each(function() {
                loadProductsForCategory(this);
            });
        });
    </script>
@endpush
