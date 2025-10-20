@extends('layouts.app')

@section('content')
    <section class="content-header">
        {{-- ... your breadcrumbs ... --}}
    </section>
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('lot.add_title') }}</h3>
                    </div>
                    <form action="{{ route('lots.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            {{-- Lot Master Information --}}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="lot_number">{{ __('lot.lot_number') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="lot_number" name="lot_number"
                                        value="{{ old('lot_number') }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="entry_date">{{ __('lot.entry_date') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="date" class="form-control" id="entry_date" name="entry_date"
                                        value="{{ old('entry_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="remarks">{{ __('lot.remarks') }}</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
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
                                    {{-- Rows are added here by JS --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="action" value="save_draft" class="btn btn-secondary">
                                <i class="fas fa-save"></i> {{ __('buttons.save_draft') }}
                            </button>
                            <button type="submit" name="action" value="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> {{ __('buttons.submit_for_approval') }}
                            </button>
                            <a href="{{ route('lots.index') }}" class="btn btn-default float-end"><i
                                    class="fas fa-ban"></i> {{ __('buttons.cancel') }}</a>
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
            let rowIdx = 0;
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

            // Remove row
            $('#lot-details-body').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Load products on category change
            $('#lot-details-body').on('change', '.category-select', function() {
                const categoryId = $(this).val();
                const productSelect = $(this).closest('tr').find('.product-select');
                const urlTemplate = "{{ route('products.by_category', ['categoryId' => ':id']) }}";
                const finalUrl = urlTemplate.replace(':id', categoryId);

                productSelect.prop('disabled', true).html('<option value="">Loading...</option>');

                if (!categoryId) {
                    productSelect.html('<option value="">Select Category First</option>');
                    return;
                }

                $.ajax({
                    url: finalUrl,
                    type: 'GET',
                    success: function(products) {
                        let options = '<option value="">Select Product</option>';
                        products.forEach(function(product) {
                            options +=
                                `<option value="${product.id}">${product.name}</option>`;
                        });
                        productSelect.html(options).prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load products for this category.');
                        productSelect.html('<option value="">Error</option>');
                    }
                });
            });

            // Add one row by default
            $('#add-row').click();
        });
    </script>
@endpush
