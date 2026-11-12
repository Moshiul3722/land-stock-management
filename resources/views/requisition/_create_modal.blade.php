<form id="requisition-create-form" action="{{ route('requisitions.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="form-group">
            <label>Product Category</label>
            <select class="form-control select2" id="modal-category-filter">
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <table class="table table-bordered" id="modal-requisition-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <div class="form-group">
            <label>Remarks</label>
            <textarea class="form-control" name="remarks" rows="2"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Requisition</button>
    </div>
</form>
