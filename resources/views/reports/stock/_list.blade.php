<table id="current-stock-table" class="table table-bordered table-striped table-hover" style="width:100%;">
    <thead>
        <tr>
            <th>{{ __('stock.table_sl') }}</th>
            <th>{{ __('stock.table_category') }}</th>
            <th>{{ __('subcategory.title') }}</th>
            <th>{{ __('stock.table_product') }}</th>
            <th>{{ __('stock.brand_spec') }}</th>
            <th>{{ __('stock.table_quantity') }}</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($stockSummary as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->category_name }}</td>
                <td>{{ $item->sub_category_name ?? '-' }}</td>
                <td>{{ $item->product_name }}</td>
                <td>
                    {{-- Display Brand/Spec --}}
                    <strong>{{ $item->brand ?? 'N/A' }}</strong><br>
                    <small class="text-muted">{{ $item->specification }}</small>
                </td>
                {{-- Logic: 0 = Red, < 10 = Yellow, >= 10 = Green --}}
                <td class="text-center font-weight-bold {{ $item->total_quantity == 0 ? 'text-danger' : ($item->total_quantity < 10 ? 'text-warning' : 'text-success') }}">
                    {{ $item->total_quantity }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td colspan="5" class="text-right">Total Quantity in View:</td>
            <td class="text-center">{{ $stockSummary->sum('total_quantity') }}</td>
        </tr>
    </tfoot>
</table>
