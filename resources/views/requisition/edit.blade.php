@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('requisition.edit_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href={{ route('requisitions.index') }}>{{ __('requisition.list_title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        {{-- Filter Card (For adding NEW items) --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('requisition.select_product') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="category_filter">{{ __('category.title') }}</label>
                                <select class="form-control select2" id="category_filter">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sub_category_filter">Sub Category</label>
                                <select class="form-control select2" id="sub_category_filter" disabled>
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="product_filter">{{ __('product.title') }}</label>
                                <select class="form-control select2" id="product_filter" disabled>
                                    <option value="">Select Category First</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-success float-end" type="button" id="add-to-list-btn">
                            <i class="fas fa-plus"></i> {{ __('buttons.add_to_list') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Requisition List Card --}}
        <div class="row">
            <div class="col-md-12">
                <form id="requisition-form" action="{{ route('requisitions.update', $requisition) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('requisition.list_title') }}</h3>
                        </div>
                        <div class="card-body">
                            {{-- Date --}}
                            <div class="form-group col-md-4 mb-3">
                                <label>Requisition Date</label>
                                <input type="date" name="requisition_date" class="form-control"
                                    value="{{ $requisition->requisition_date->format('Y-m-d') }}" required>
                            </div>

                            <table id="requisition-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('stock.table_category') }}</th>
                                        <th>Sub Category</th> {{-- New Column --}}
                                        <th>{{ __('stock.table_product') }}</th>
                                        <th width="15%">{{ __('requisition.required_amount') }}</th>
                                        <th width="10%">{{ __('stock.table_action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Load Existing Items --}}
                                    @foreach ($requisition->items as $index => $item)
                                        <tr>
                                            <td>{{ $item->product->category->name }}</td>
                                            <td>{{ optional($item->product->subCategory)->name ?? 'N/A' }}</td>
                                            {{-- New Column Data --}}
                                            <td>
                                                {{ $item->product->name }}
                                                <input type="hidden" name="details[{{ $index }}][product_id]"
                                                    value="{{ $item->product_id }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control"
                                                    name="details[{{ $index }}][quantity]" min="1"
                                                    value="{{ $item->quantity }}" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                        class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <hr>
                            <div class="form-group col-md-12">
                                <label for="remarks">{{ __('lot.remarks') }}</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ old('remarks', $requisition->remarks) }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" id="save-draft-btn" class="btn btn-secondary">
                                <i class="fas fa-save"></i> {{ __('buttons.save_draft') }}
                            </button>
                            <button type="submit" id="submit-approval-btn" class="btn btn-primary float-end">
                                <i class="fas fa-paper-plane"></i> {{ __('buttons.submit_for_approval') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var requisitionTable = $('#requisition-table').DataTable({
                "paging": false,
                "info": false,
                "searching": false
            });

            // We need a counter higher than existing items to avoid index collision
            var rowCounter = {{ $requisition->items->count() + 1 }};

            // --- Dependent Dropdown Logic ---

            // 1. Category Change
            $('#category_filter').on('change', function() {
                const categoryId = $(this).val();
                const subCategorySelect = $('#sub_category_filter');
                const productSelect = $('#product_filter');

                // Reset Sub Category & Product
                subCategorySelect.html('<option value="">Select Sub Category</option>').prop('disabled',
                    true);
                productSelect.html('<option value="">Select Product</option>').prop('disabled', true);

                if (!categoryId) {
                    productSelect.html('<option value="">Select Category First</option>');
                    return;
                }

                // A. Fetch Sub Categories
                $.ajax({
                    url: "{{ route('get.sub_categories', ':id') }}".replace(':id', categoryId),
                    type: 'GET',
                    success: function(data) {
                        if (data.length > 0) {
                            $.each(data, function(key, value) {
                                subCategorySelect.append('<option value="' + value.id +
                                    '">' + value.name + '</option>');
                            });
                            subCategorySelect.prop('disabled', false);
                        } else {
                            subCategorySelect.append(
                                '<option value="">No Sub Categories</option>');
                        }
                    }
                });

                // B. Fetch Products (By Category)
                const url = "{{ route('products.by_category', ['categoryId' => ':id']) }}".replace(':id',
                    categoryId);

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
                        toastr.error('Failed to load products.');
                    }
                });
            });

            // 2. Sub Category Change
            $('#sub_category_filter').on('change', function() {
                const subCategoryId = $(this).val();
                const productSelect = $('#product_filter');

                // Reset Product Dropdown
                productSelect.empty().append('<option value="">Select Product</option>').prop('disabled',
                    true);

                // If cleared, revert to loading all products by category
                if (!subCategoryId) {
                    let categoryId = $('#category_filter').val();
                    if (categoryId) {
                        const url = "{{ route('products.by_category', ['categoryId' => ':id']) }}".replace(
                            ':id', categoryId);
                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(products) {
                                $.each(products, function(key, value) {
                                    productSelect.append('<option value="' + value.id +
                                        '">' + value.name + '</option>');
                                });
                                productSelect.prop('disabled', false);
                            }
                        });
                    }
                    return;
                }

                // Fetch Products (By Sub Category)
                const url = "{{ route('products.by_sub_category', ':id') }}".replace(':id', subCategoryId);

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
                        toastr.error('Failed to load products.');
                    }
                });
            });

            // 3. "Add to List" Button Logic
            $('#add-to-list-btn').on('click', function() {
                const productSelect = $('#product_filter');
                const productId = productSelect.val();

                if (!productId) {
                    toastr.error('Please select a product first.');
                    return;
                }

                const productName = productSelect.find('option:selected').text();
                const categoryName = $('#category_filter').find('option:selected').text();

                // Handle Sub Category Name display
                let subCategoryName = $('#sub_category_filter').find('option:selected').text();
                if ($('#sub_category_filter').val() == "" || $('#sub_category_filter').prop('disabled')) {
                    subCategoryName = "N/A";
                }

                rowCounter++;

                requisitionTable.row.add([
                    categoryName,
                    subCategoryName, // Added Sub Cat column data
                    productName +
                    `<input type="hidden" name="details[${rowCounter}][product_id]" value="${productId}">`,
                    `<input type="number" class="form-control" name="details[${rowCounter}][quantity]" min="1" value="1" required>`,
                    `<button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>`
                ]).draw(false);
            });

            // 4. Delete Row Logic
            $('#requisition-table tbody').on('click', '.remove-row', function() {
                requisitionTable.row($(this).parents('tr')).remove().draw(false);
            });

            // 5. Form Submission Logic
            $('#requisition-form').on('submit', function(e) {
                var action = $(document.activeElement).attr('id');
                e.preventDefault();

                if (requisitionTable.rows().count() === 0) {
                    toastr.error("{{ __('requisition.toastr_must_add_one') }}");
                    return false;
                }

                var title = (action === 'save-draft-btn') ?
                    '{{ __('requisition.sweetalert_draft_save_title') }}' :
                    '{{ __('requisition.sweetalert_save_title') }}';
                var text = (action === 'save-draft-btn') ?
                    '{{ __('requisition.sweetalert_draft_save_text') }}' :
                    '{{ __('requisition.sweetalert_save_text') }}';

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('requisition.sweetalert_confirm_button') }}',
                    cancelButtonText: '{{ __('requisition.sweetalert_cancel_button') }}',
                }).then((result) => {
                    if (result.isConfirmed) {
                        var form = $('#requisition-form');
                        var actionValue = (action === 'save-draft-btn') ? 'save_draft' : 'submit';

                        form.find('input[name="action"]').remove();
                        form.append(`<input type="hidden" name="action" value="${actionValue}" />`);

                        form[0].submit();
                    }
                });
            });
        });
    </script>
@endpush
