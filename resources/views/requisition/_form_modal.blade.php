<form id="requisition-form" action="{{ route('requisitions.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Select Product</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Category</label>
                        <select class="form-control select2" id="category_filter" style="width: 100%;">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Product</label>
                        <select class="form-control select2" id="product_filter" disabled style="width: 100%;">
                            <option value="">Select Category First</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <button class="btn btn-success w-100" type="button" id="add-to-list-btn">Add</button>
                    </div>
                </div>
            </div>
        </div>

        <table id="requisition-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th width="20%">Quantity</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <div class="form-group mt-3">
            <label>Remarks</label>
            <textarea class="form-control" name="remarks" rows="3"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" id="save-draft-btn" class="btn btn-secondary">Save Draft</button>
        <button type="submit" id="submit-approval-btn" class="btn btn-primary">Submit</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        $('.select2').select2({ dropdownParent: $('#genericModal') });
        
        var requisitionTable = $('#requisition-table').DataTable({ "paging": false, "info": false, "searching": false });
        var rowCounter = 0;

        $('#category_filter').on('change', function() {
            const categoryId = $(this).val();
            const productSelect = $('#product_filter');
            productSelect.html('<option value="">Select Product</option>').prop('disabled', true);
            if (!categoryId) return;

            $.ajax({
                url: "{{ route('products.by_category', ['categoryId' => ':id']) }}".replace(':id', categoryId),
                type: 'GET',
                success: function(products) {
                    $.each(products, function(key, value) {
                        productSelect.append(`<option value="${value.id}" data-unit="${value.product_unit || ''}">${value.name}</option>`);
                    });
                    productSelect.prop('disabled', false);
                }
            });
        });

        $('#add-to-list-btn').on('click', function() {
            const productSelect = $('#product_filter');
            const productId = productSelect.val();
            if (!productId) return toastr.error('Select a product.');

            rowCounter++;
            requisitionTable.row.add([
                $('#category_filter option:selected').text(),
                productSelect.find('option:selected').text() + `<input type="hidden" name="details[${rowCounter}][product_id]" value="${productId}">`,
                `<input type="number" class="form-control" name="details[${rowCounter}][quantity]" value="1" required>`,
                `<button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>`
            ]).draw(false);
        });

        $('#requisition-table tbody').on('click', '.remove-row', function() {
            requisitionTable.row($(this).parents('tr')).remove().draw(false);
        });
    });
</script>
