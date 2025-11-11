<table id="lots-table" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{{ __('lot.table_sl') }}</th>
            <th>{{ __('lot.table_lot_number') }}</th>
            <th>{{ __('lot.purchase_type') }}</th>
            <th>{{ __('lot.table_entry_date') }}</th>
            <th>{{ __('lot.table_status') }}</th>
            <th>{{ __('lot.table_action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lots as $lot)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $lot->lot_number }}</td>
                <td>{{ $lot->purchaseType->name ?? 'N/A' }}</td>
                <td>{{ $lot->entry_date->format('d-M-Y') }}</td>
                <td class="text-center">
                    @php
                        $badgeClass = 'bg-info'; // Default
                        if ($lot->status === 'draft') { $badgeClass = 'bg-secondary'; }
                        elseif ($lot->status === 'pending') { $badgeClass = 'bg-warning text-dark'; }
                        elseif ($lot->status === 'approved') { $badgeClass = 'bg-success'; }
                        elseif ($lot->status === 'rejected') { $badgeClass = 'bg-danger'; }
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($lot->status) }}</span>
                </td>
                <td class="text-center">
                    <a href="{{ route('lots.show', $lot) }}" class="btn btn-info btn-sm" title="View"><i class="fa fa-eye"></i></a>
                    @if($lot->status === 'draft')
                        <a href="{{ route('lots.edit', $lot) }}" class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $lot->id }}" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>