@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('requisition.create_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.create') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        {{-- Filter Card --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('requisition.select_product') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="category_filter">{{ __('category.title') }}</label>
                                <select class="form-control select2" id="category_filter">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-5">
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
                <form id="requisition-form" action="{{ route('requisitions.store') }}" method="POST">
                    @csrf
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('requisition.list_title') }}</h3>
                        </div>
                        <div class="card-body">
                            <table id="requisition-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('stock.table_category') }}</th>
                                        <th>{{ __('stock.table_product') }}</th>
                                        <th>{{ __('requisition.stock_status') }}</th>
                                        <th>{{ __('requisition.required_amount') }}</th>
                                        <th>{{ __('stock.table_action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Rows will be added by JS --}}
                                </tbody>
                            </table>
                            <hr>
                            <div class="form-group col-md-12">
                                <label for="remarks">{{ __('lot.remarks') }}</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ old('remarks') }}</textarea>
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
    // Initialize the DataTable (it will be empty at first)
    var requisitionTable = $('#requisition-table').DataTable({
        "paging": false,
        "info": false,
        "searching": false
    });
    var rowCounter = 0;

    // 1. Dependent Dropdown Logic
    $('#category_filter').on('change', function() {
        const categoryId = $(this).val();
        const productSelect = $('#product_filter');
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
                    options += `<option value="${product.id}" data-category-name="${productSelect.find(':selected').text()}">${product.name}</option>`;
                });
                productSelect.html(options).prop('disabled', false);
            },
            error: function() { toastr.error('Failed to load products.'); }
        });
    });

    // 2. "Add to List" Button Logic
    $('#add-to-list-btn').on('click', function() {
        const productSelect = $('#product_filter');
        const productId = productSelect.val();
        if (!productId) {
            toastr.error('Please select a product first.');
            return;
        }

        const productName = productSelect.find('option:selected').text();
        const categoryName = $('#category_filter').find('option:selected').text();
        const stockCheckUrl = "{{ route('stock.check_status', ['product' => ':id']) }}".replace(':id', productId);

        // Check stock status via AJAX
        $.ajax({
            url: stockCheckUrl,
            type: 'GET',
            success: function(response) {
                rowCounter++;
                // Add new row to DataTable
                requisitionTable.row.add([
                    categoryName,
                    productName + `<input type="hidden" name="details[${rowCounter}][product_id]" value="${productId}">`,
                    `<span class="${response.class} stock-status">${response.status}</span>` +
                    `<input type="hidden" name="details[${rowCounter}][status]" value="${response.status}">`,
                    `<input type="number" class="form-control" name="details[${rowCounter}][quantity]" min="1" required>`,
                    `<button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>`
                ]).draw(false);
            },
            error: function() { toastr.error('Failed to check stock status.'); }
        });
    });

    // 3. Delete Row Logic
    $('#requisition-table tbody').on('click', '.remove-row', function() {
        requisitionTable.row($(this).parents('tr')).remove().draw(false);
    });

    // 4. Form Submission Logic
    $('#requisition-form').on('submit', function(e) {
        var action = $(document.activeElement).attr('id'); // Get the ID of the button clicked

        // Prevent submission to run checks
        e.preventDefault();

        // Check 1: Ensure table is not empty
        if (requisitionTable.rows().count() === 0) {
            toastr.error("{{ __('requisition.toastr_must_add_one') }}");
            return false;
        }

        // Check 2 (CRITICAL): If submitting, check for "Not Available" items
        if (action === 'submit-approval-btn') {
            var hasUnavailable = false;
            $('#requisition-table .stock-status').each(function() {
                if ($(this).text() === 'Not Available') {
                    hasUnavailable = true;
                }
            });

            if (hasUnavailable) {
                toastr.error("{{ __('requisition.toastr_cannot_submit') }}");
                return false;
            }
        }

        // If all checks pass, show confirmation
        var title = (action === 'save-draft-btn') ? '{{ __("requisition.sweetalert_draft_save_title") }}' : '{{ __("requisition.sweetalert_save_title") }}';
        var text = (action === 'save-draft-btn') ? '{{ __("requisition.sweetalert_draft_save_text") }}' : '{{ __("requisition.sweetalert_save_text") }}';

        Swal.fire({
            title: title,
            text: text,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '{{ __("requisition.sweetalert_confirm_button") }}',
            cancelButtonText: '{{ __("requisition.sweetalert_cancel_button") }}',
        }).then((result) => {
            if (result.isConfirmed) {
                // Add the action to the form and submit
                var actionValue = (action === 'save-draft-btn') ? 'save_draft' : 'submit';
                $(this).append(`<input type="hidden" name="action" value="${actionValue}" />`);
                this.submit();
            }
        });
    });
});
</script>
@endpush
