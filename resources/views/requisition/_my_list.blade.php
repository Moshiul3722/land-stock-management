<table id="my-req-table" class="table table-hover align-middle">
    <thead>
        <tr>
            <th class="text-center">{{ __('lot.table_sl') }}</th>
            <th>{{ __('requisition.requisition_date') }}</th>
            <th class="text-center">{{ __('requisition.items_count') }}</th>
            <th class="text-center">{{ __('lot.table_status') }}</th>
            <th width="150" class="text-center">{{ __('lot.table_action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($requisitions as $req)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $req->requisition_date->format('d-M-Y') }}</td>
                <td class="text-center">{{ $req->items->count() }}</td>
                <td class="text-center">
                    @php
                        $badgeClass = 'bg-warning text-dark';
                        if (in_array($req->status, ['approved', 'delivered', 'completed', 'processed'])) {
                            $badgeClass = 'bg-success';
                        } elseif ($req->status === 'rejected') {
                            $badgeClass = 'bg-danger';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}">
                        {{ ucfirst($req->status) }}
                    </span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-info btn-sm view-req-btn shadow-sm"
                        data-url="{{ route('requisitions.get_details', $req->id) }}" data-id="{{ $req->id }}" title="View Details">
                        <i class="fa fa-eye"></i>
                    </button>

                    @if ($req->status === 'draft')
                        <button type="button" class="btn btn-warning btn-sm edit-req-btn shadow-sm"
                            data-url="{{ route('requisitions.edit_modal', $req->id) }}" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>

                        <button type="button" class="btn btn-danger btn-sm delete-req-btn shadow-sm"
                            data-id="{{ $req->id }}" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
