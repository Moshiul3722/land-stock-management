<div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ __('product.product_list') }}</h3>
    </div>
</div>

<div class="card-body">
    <div class="row"></div>

    <table id="products-table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>SL</th>
                <th>Name</th>
                <th>Category</th>
                <th>Purchase Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->purchaseType->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm" title="View"><i
                                class="fa fa-eye"></i></a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm"
                            title="Edit"><i class="fa fa-edit"></i></a>
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $product->id }}"
                            title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
