<table id="consumption-table" class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>SL</th>
            <th>Department</th>
            <th>Product Details</th>
            <th>Purchase Type</th>
            <th>Requisitions Count</th>
            <th class="text-right">Total Qty Issued</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $row)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="font-weight-bold">{{ $row->department_name ?? 'General / No Dept' }}</td>
                <td>
                    {{ $row->product_name }} <br>
                    <small class="text-muted">{{ $row->category_name }}</small>
                </td>
                <td><span class="badge bg-info">{{ $row->purchase_type_name ?? 'N/A' }}</span></td>
                <td class="text-center">{{ $row->requisition_count }}</td>
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
