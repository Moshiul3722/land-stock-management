<table id="current-stock-table" class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>{{ __('stock.table_sl') }}</th>
            <th>{{ __('stock.table_category') }}</th>
            <th>{{ __('stock.table_product') }}</th>
            <th>{{ __('stock.table_purchase_type') }}</th>
            <th>{{ __('stock.table_quantity') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($stockSummary as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->category_name }}</td>
                <td>{{ $item->product_name }}</td>
                <td>
                    <span class="badge bg-info text-dark">{{ $item->purchase_type_name ?? 'N/A' }}</span>
                </td>
                <td class="text-center">
                    {{ $item->total_quantity }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td colspan="4" class="text-right">Total Quantity in View:</td>
            <td class="text-center">{{ $stockSummary->sum('total_quantity') }}</td>
        </tr>
    </tfoot>
</table>
