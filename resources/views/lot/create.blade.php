@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.add_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href={{ route('lots.index') }}>{{ __('breadcrumb.list') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.create') }}</li>
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
                        <h3 class="card-title">{{ __('lot.add_title') }}</h3>
                    </div>
                    {{-- Form with ID for JS handling --}}
                    <form id="lot-form" action="{{ route('lots.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            {{-- Lot Master Information --}}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="lot_number">{{ __('lot.lot_number') }} <span style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="lot_number" name="lot_number"
                                        value="{{ old('lot_number') }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="entry_date">{{ __('lot.entry_date') }} <span style="color: red;">*</span></label>
                                    <input type="date" class="form-control" id="entry_date" name="entry_date"
                                        value="{{ old('entry_date', date('Y-m-d')) }}" required>
                                </div>

                                {{-- Fiscal Year Dropdown --}}
                                <div class="form-group col-md-4">
                                    <label for="fiscal_year_id">Fiscal Year <span style="color: red;">*</span></label>
                                    <select class="form-control js-example-basic-single" name="fiscal_year_id" required>
                                        <option value="">Select Fiscal Year</option>
                                        @foreach ($fiscalYears as $year)
                                            <option value="{{ $year->id }}"
                                                {{ (old('fiscal_year_id') == $year->id || (isset($currentFiscalYearId) && $currentFiscalYearId == $year->id)) ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Purchase Type Dropdown --}}
                                <div class="form-group col-md-12">
                                    <label for="product_purchase_type_id">{{ __('lot.purchase_type') }} <span style="color: red;">*</span></label>
                                    <select class="form-control js-example-basic-single custom-select"
                                        id="product_purchase_type_id" name="product_purchase_type_id" required>
                                        <option value="">Select Purchase Type</option>
                                        @foreach ($purchaseTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('product_purchase_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}</option>
                                        @endforeach
                                    </select>
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
                                        <th width="15%">{{ __('lot.table_category') }} <span class="text-danger">*</span></th>
                                        <th width="15%">Sub Category <span class="text-danger">*</span></th>
                                        <th width="15%">{{ __('lot.table_product') }} <span class="text-danger">*</span></th>
                                        <th width="15%">Brand< <span class="text-danger">*</span></th>
                                        <th width="15%">Specification <span class="text-danger">*</span></th>
                                        <th width="10%">{{ __('lot.table_quantity') }} <span class="text-danger">*</span></th>
                                        <th width="10%">Price <span class="text-danger">*</span></th>
                                        <th class="text-center" width="5%"><button type="button" id="add-row"
                                                class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody id="lot-details-body">
                                    {{-- Rows are added here by JS --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button type="submit" id="save-draft-btn" name="action" value="save_draft"
                                class="btn btn-secondary">
                                <i class="fas fa-save"></i> {{ __('buttons.save_draft') }}
                            </button>
                            <button type="submit" id="submit-approval-btn" name="action" value="submit"
                                class="btn btn-primary">
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
            // Initialize Master Select2
            $('.js-example-basic-single').select2();

            let rowIdx = 0;

            // --- 1. Autocomplete Initialization Function ---
            function initAutocomplete(row) {
                row.find('.autocomplete-brand').autocomplete({
                    source: function(request, response) {
                        $.getJSON("{{ route('lots.autocomplete') }}", {
                            term: request.term,
                            type: 'brand'
                        }, response);
                    },
                    minLength: 1
                });

                row.find('.autocomplete-spec').autocomplete({
                    source: function(request, response) {
                        $.getJSON("{{ route('lots.autocomplete') }}", {
                            term: request.term,
                            type: 'specification'
                        }, response);
                    },
                    minLength: 1
                });
            }

            // --- 2. Select2 Initialization Function ---
            function initSelect2(row) {
                row.find('.select2-dynamic').select2({
                    width: '100%',
                    placeholder: "Select option",
                    allowClear: true
                });
            }

            // --- 3. Add Row Functionality ---
            $('#add-row').on('click', function() {
                rowIdx++;
                let newRowHtml = `
            <tr id="R${rowIdx}">
                <td>
                    <select class="form-control category-select select2-dynamic" name="details[${rowIdx}][category_id]" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="form-control sub-category-select select2-dynamic" name="details[${rowIdx}][sub_category_id]" disabled>
                        <option value="">Select Sub Category</option>
                    </select>
                </td>
                <td>
                    <select class="form-control product-select select2-dynamic" name="details[${rowIdx}][product_id]" required disabled>
                        <option value="">Select Product</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control autocomplete-brand" name="details[${rowIdx}][brand]" placeholder="Brand">
                </td>
                <td>
                    <input type="text" class="form-control autocomplete-spec" name="details[${rowIdx}][specification]" placeholder="Specification">
                </td>
                <td><input type="number" class="form-control" name="details[${rowIdx}][quantity]" min="1" required></td>
                <td><input type="number" class="form-control" name="details[${rowIdx}][unit_price]" step="0.01" min="0" required></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
            </tr>
        `;

                let newRow = $(newRowHtml);
                $('#lot-details-body').append(newRow);

                // Initialize plugins on the new row
                initSelect2(newRow);
                initAutocomplete(newRow);
            });

            // --- 4. Remove Row ---
            $('#lot-details-body').on('click', '.remove-row', function(e) {
                e.preventDefault();
                var rowToRemove = $(this).closest('tr');
                Swal.fire({
                    title: "{{ __('sweetalert.confirmation') }}",
                    text: "{{ __('sweetalert.delete_revert_text') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        rowToRemove.remove();
                    }
                });
            });

            // --- 5. Form Submissions ---
            function prepareFormAndSubmit(actionValue) {
                var form = $('#lot-form');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }
                form.find('select').prop('disabled', false); // Enable all selects to ensure data is sent
                form.find('input[name="action"]').remove();
                form.append('<input type="hidden" name="action" value="' + actionValue + '" />');
                form.submit();
            }

            $('#save-draft-btn').on('click', function(e) {
                e.preventDefault();
                prepareFormAndSubmit('save_draft');
            });

            $('#submit-approval-btn').on('click', function(e) {
                e.preventDefault();
                var form = $('#lot-form');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                Swal.fire({
                    title: "{{ __('sweetalert.submit_for_approval_title') }}",
                    text: "{{ __('product.lot_sa_submit_notice_text') }}",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Submit it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        prepareFormAndSubmit('submit');
                    }
                });
            });

            // --- 6. Dependent Dropdowns Logic ---

            // A. Category Change -> Load Sub Categories AND Products
            $('#lot-details-body').on('change', '.category-select', function() {
                const categoryId = $(this).val();
                const row = $(this).closest('tr');
                const subCatSelect = row.find('.sub-category-select');
                const productSelect = row.find('.product-select');

                // Reset and Disable
                subCatSelect.empty().append('<option value="">Select Sub Category</option>').prop('disabled',
                    true);
                productSelect.empty().append('<option value="">Select Product</option>').prop('disabled',
                    true);

                if (!categoryId) return;

                // 1. Fetch Sub Categories
                $.ajax({
                    url: "{{ route('get.sub_categories', ':id') }}".replace(':id', categoryId),
                    type: 'GET',
                    success: function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                subCatSelect.append('<option value="' + value.id +
                                    '">' + value.name + '</option>');
                            });
                            subCatSelect.prop('disabled', false);
                        }
                    }
                });

                // 2. Fetch Products (Based on Category)
                $.ajax({
                    url: "{{ route('products.by_category', ':id') }}".replace(':id', categoryId),
                    type: 'GET',
                    success: function(products) {
                        $.each(products, function(key, value) {
                            productSelect.append('<option value="' + value.id + '">' +
                                value.name + '</option>');
                        });
                        productSelect.prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load products.');
                    }
                });
            });

            // B. Sub Category Change -> Refine Products
            $('#lot-details-body').on('change', '.sub-category-select', function() {
                const subCategoryId = $(this).val();
                const row = $(this).closest('tr');
                const productSelect = row.find('.product-select');

                // Reset Product
                productSelect.empty().append('<option value="">Select Product</option>').prop('disabled',
                    true);

                if (!subCategoryId) {
                    // Optional: Revert to Category-based products if needed
                    return;
                }

                const url = "{{ url('/get-products-by-sub-category') }}/" + subCategoryId;

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(products) {
                        $.each(products, function(key, value) {
                            productSelect.append('<option value="' + value.id + '">' +
                                value.name + '</option>');
                        });
                        productSelect.prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load products for this sub-category.');
                    }
                });
            });

            // Add initial row
            $('#add-row').click();
        });
    </script>
@endpush