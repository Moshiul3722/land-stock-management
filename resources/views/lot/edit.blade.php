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

                    {{-- Add ID for JS handling --}}
                    <form id="lot-form" action="{{ route('lots.update', $lot) }}" method="POST">
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
                                    <label for="product_purchase_type_id">{{ __('lot.purchase_type') }} <span
                                            style="color: red;">*</span></label>
                                    <select class="form-control js-example-basic-single custom-select"
                                        id="product_purchase_type_id" name="product_purchase_type_id" required>
                                        <option value="">Select Purchase Type</option>
                                        @foreach ($purchaseTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('product_purchase_type_id', $lot->product_purchase_type_id) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}</option>
                                        @endforeach
                                    </select>
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
                                        <th width="15%">{{ __('lot.table_category') }}</th>
                                        <th width="15%">{{ __('lot.table_sub_category') }}</th>
                                        <th width="15%">{{ __('lot.table_product') }}</th>
                                        <th width="15%">{{ __('lot.brand') }}</th>
                                        <th width="15%">{{ __('lot.specification') }}</th>
                                        <th width="10%">{{ __('lot.table_quantity') }}</th>
                                        <th class="text-center" width="5%"><button type="button" id="add-row"
                                                class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody id="lot-details-body">
                                    @foreach ($lot->lotDetails as $index => $detail)
                                        <tr id="R{{ $index }}">
                                            <td>
                                                <select class="form-control category-select select2-dynamic"
                                                    name="details[{{ $index }}][category_id]" required>
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
                                                {{-- Sub Category (Will be loaded via JS based on data attributes) --}}
                                                <select class="form-control sub-category-select select2-dynamic"
                                                    name="details[{{ $index }}][sub_category_id]"
                                                    data-selected="{{ optional($detail->product)->sub_category_id }}">
                                                    <option value="">Select Sub Category</option>
                                                </select>
                                            </td>
                                            <td>
                                                {{-- Product (Will be loaded via JS based on data attributes) --}}
                                                <select class="form-control product-select select2-dynamic"
                                                    name="details[{{ $index }}][product_id]"
                                                    data-selected="{{ $detail->product_id }}" required>
                                                    <option value="">Loading...</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control autocomplete-brand"
                                                    name="details[{{ $index }}][brand]"
                                                    value="{{ $detail->brand }}" placeholder="Brand">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control autocomplete-spec"
                                                    name="details[{{ $index }}][specification]"
                                                    value="{{ $detail->specification }}" placeholder="Specification">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control"
                                                    name="details[{{ $index }}][quantity]" min="1"
                                                    value="{{ $detail->quantity }}" required>
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
                            <button type="submit" id="save-draft-btn" name="action" value="save_draft"
                                class="btn btn-secondary">
                                <i class="fas fa-save"></i> {{ __('buttons.save_draft') }}
                            </button>
                            <button type="submit" id="submit-approval-btn" name="action" value="submit"
                                class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> {{ __('buttons.submit_for_approval') }}
                            </button>
                            <a href="{{ route('lots.index') }}" class="btn btn-default float-end">
                                <i class="fas fa-ban"></i> {{ __('buttons.cancel') }}
                            </a>
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
            // Master select2
            $('.js-example-basic-single').select2();

            let rowIdx = {{ $lot->lotDetails->count() }};

            // --- 1. Autocomplete Logic ---
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

            // --- 2. Select2 Initialization ---
            function initSelect2(row) {
                row.find('.select2-dynamic').select2({
                    width: '100%',
                    placeholder: "Select option",
                    allowClear: true
                });
            }

            // Initialize existing rows
            $('tr[id^="R"]').each(function() {
                let row = $(this);
                initSelect2(row);
                initAutocomplete(row);

                // Trigger data loading for existing rows
                let catSelect = row.find('.category-select');
                let subCatId = row.find('.sub-category-select').data('selected');
                let prodId = row.find('.product-select').data('selected');

                if (catSelect.val()) {
                    loadSubCategories(row, catSelect.val(), subCatId);

                    // If we have a sub-category ID, load products by sub-cat, otherwise by cat
                    if (subCatId) {
                        loadProductsBySubCat(row, subCatId, prodId);
                    } else {
                        loadProductsByCat(row, catSelect.val(), prodId);
                    }
                }
            });

            // --- 3. Add Row ---
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
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
                    </tr>
                `;

                let newRow = $(newRowHtml);
                $('#lot-details-body').append(newRow);
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

            // --- 5. Submit Logic ---
            function prepareFormAndSubmit(actionValue) {
                var form = $('#lot-form');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }
                form.find('select').prop('disabled', false);
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

            // --- 6. Helper Functions for Dropdowns ---

            function loadSubCategories(row, categoryId, selectedId = null) {
                let subCatSelect = row.find('.sub-category-select');
                if (!categoryId) {
                    subCatSelect.empty().append('<option value="">Select Sub Category</option>').prop('disabled',
                        true);
                    return;
                }

                $.ajax({
                    url: "{{ route('get.sub_categories', ':id') }}".replace(':id', categoryId),
                    type: 'GET',
                    success: function(data) {
                        subCatSelect.empty().append('<option value="">Select Sub Category</option>');
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                let isSelected = (selectedId && selectedId == value.id) ?
                                    'selected' : '';
                                subCatSelect.append('<option value="' + value.id + '" ' +
                                    isSelected + '>' + value.name + '</option>');
                            });
                            subCatSelect.prop('disabled', false);
                        } else {
                            subCatSelect.append('<option value="">No Sub Categories</option>');
                        }
                    }
                });
            }

            function loadProductsByCat(row, categoryId, selectedId = null) {
                let productSelect = row.find('.product-select');

                $.ajax({
                    url: "{{ route('products.by_category', ':id') }}".replace(':id', categoryId),
                    type: 'GET',
                    success: function(products) {
                        productSelect.empty().append('<option value="">Select Product</option>');
                        $.each(products, function(key, value) {
                            let isSelected = (selectedId && selectedId == value.id) ?
                                'selected' : '';
                            productSelect.append('<option value="' + value.id + '" ' +
                                isSelected + '>' + value.name + '</option>');
                        });
                        productSelect.prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load products.');
                    }
                });
            }

            function loadProductsBySubCat(row, subCategoryId, selectedId = null) {
                let productSelect = row.find('.product-select');
                const url = "{{ url('/get-products-by-sub-category') }}/" + subCategoryId;

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(products) {
                        productSelect.empty().append('<option value="">Select Product</option>');
                        $.each(products, function(key, value) {
                            let isSelected = (selectedId && selectedId == value.id) ?
                                'selected' : '';
                            productSelect.append('<option value="' + value.id + '" ' +
                                isSelected + '>' + value.name + '</option>');
                        });
                        productSelect.prop('disabled', false);
                    },
                    error: function() {
                        toastr.error('Failed to load products.');
                    }
                });
            }

            // --- 7. Event Listeners for Dropdowns ---

            // A. Category Change
            $('#lot-details-body').on('change', '.category-select', function() {
                const categoryId = $(this).val();
                const row = $(this).closest('tr');
                const subCatSelect = row.find('.sub-category-select');
                const productSelect = row.find('.product-select');

                // Reset children
                subCatSelect.empty().append('<option value="">Select Sub Category</option>').prop(
                    'disabled', true);
                productSelect.empty().append('<option value="">Select Product</option>').prop('disabled',
                    true);

                if (!categoryId) return;

                loadSubCategories(row, categoryId);
                loadProductsByCat(row, categoryId);
            });

            // B. Sub Category Change
            $('#lot-details-body').on('change', '.sub-category-select', function() {
                const subCategoryId = $(this).val();
                const row = $(this).closest('tr');
                const productSelect = row.find('.product-select');

                productSelect.empty().append('<option value="">Select Product</option>').prop('disabled',
                    true);

                if (!subCategoryId) {
                    // Revert to Category-based products if Sub Cat cleared
                    let catId = row.find('.category-select').val();
                    if (catId) loadProductsByCat(row, catId);
                    return;
                }

                loadProductsBySubCat(row, subCategoryId);
            });
        });
    </script>
@endpush
