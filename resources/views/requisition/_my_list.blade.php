<table id="my-req-table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{{ __('lot.table_sl') }}</th>
            <th>Requisition Date</th>
            <th>Items Count</th>
            <th>{{ __('lot.table_status') }}</th>
            <th>{{ __('lot.table_action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($requisitions as $req)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $req->requisition_date->format('d-M-Y') }}</td>
                <td class="text-center">{{ $req->items->count() }}</td>
                <td class="text-center">
                    <span class="badge bg-{{ $req->status == 'approved' ? 'success' : ($req->status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst($req->status) }}
                    </span>
                </td>
                <td class="text-center">
                    <a href="{{ route('requisitions.show', $req) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
