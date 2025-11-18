<table id="stock-table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{{ __('stock.table_sl') }}</th>
            <th>{{ __('stock.table_category') }}</th>
            <th>{{ __('stock.table_product') }}</th>
            <th>{{ __('stock.table_quantity') }}</th>
            <th>{{ __('stock.table_action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($stockSummary as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->category_name }}</td>
                <td>{{ $item->product_name }}</td>
                <td class="text-center">{{ $item->total_quantity }}</td>
                <td class="text-center">
                    <a href="{{ route('stock.show', $item->product_id) }}" class="btn btn-info btn-sm" title="View Details">
                        <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
