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
        @foreach ($requisitions as $req)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $req->requisition_date->format('d-M-Y') }}</td>
                <td class="text-center">{{ $req->items->count() }}</td>
                <td class="text-center">
                    <span
                        class="badge bg-{{ $req->status == 'approved' ? 'success' : ($req->status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst($req->status) }}
                    </span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-info btn-sm view-req-btn"
                        data-url="{{ route('requisitions.get_details', $req->id) }}" title="View Details">
                        <i class="fa fa-eye"></i>
                    </button>

                    @if ($req->status === 'draft')
                        {{-- Edit Button --}}
                        <a href="{{ route('requisitions.edit', $req) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>

                        {{-- Delete Button --}}
                        <button type="button" class="btn btn-danger btn-sm delete-req-btn"
                            data-id="{{ $req->id }}" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
