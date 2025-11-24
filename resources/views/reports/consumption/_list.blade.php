<table id="consumption-table" class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>{{ __('stock.table_sl') }}</th>
            <th>{{ __('product.department') }}</th>
            <th>{{ __('product.cateogory') }}</th>
            <th>{{ __('stock.table_product') }}</th>
            <th>{{ __('product.purchase_type') }}</th>
            <th class="text-right">{{ __('product.issued_count') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $row)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="font-weight-bold">{{ $row->department_name ?? 'General / No Dept' }}</td>
                <td>{{ $row->category_name }}</td>
                <td>
                    {{ $row->product_name }}
                </td>
                <td><span class="badge bg-info">{{ $row->purchase_type_name ?? 'N/A' }}</span></td>
                <td class="text-right font-weight-bold text-danger">{{ $row->total_quantity }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td colspan="5" class="text-right">Grand Total:</td>
            <td class="text-right">{{ $reportData->sum('total_quantity') }}</td>
        </tr>
    </tfoot>
</table>
