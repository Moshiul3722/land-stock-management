<table id="consumption-table" class="table table-bordered table-striped table-hover">
    <thead>
        <tr>
            <th>SL</th>
            <th>Req ID / Date</th>
            <th>Department</th>
            <th>Product Details</th>
            <th class="text-center">Requested</th>
            <th class="text-right">Received</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $row)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    <strong>#{{ $row->requisition_id }}</strong><br>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($row->issued_date)->format('d M, Y') }}</small>
                </td>
                <td class="font-weight-bold">{{ $row->department_name ?? 'General / No Dept' }}</td>
                <td>
                    {{ $row->product_name }} <br>
                    <small class="text-muted">{{ $row->category_name }}</small>
                </td>
                
                {{-- Requested Quantity --}}
                <td class="text-center">
                    {{ $row->requested_quantity }}
                </td>

                {{-- Received Quantity (Highlighted red if less than requested) --}}
                <td class="text-right font-weight-bold {{ $row->received_quantity < $row->requested_quantity ? 'text-danger' : 'text-success' }}">
                    {{ $row->received_quantity }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td colspan="4" class="text-right">Grand Total:</td>
            <td class="text-center">{{ $reportData->sum('requested_quantity') }}</td>
            <td class="text-right">{{ $reportData->sum('received_quantity') }}</td>
        </tr>
    </tfoot>
</table>