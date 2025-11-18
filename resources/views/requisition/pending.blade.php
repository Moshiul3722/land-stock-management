@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>{{ __('requisition.pending_title') }}</h1></div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">{{ __('requisition.pending_list') }}</h3></div>
            <div class="card-body">
                <table id="pending-req-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('lot.table_sl') }}</th>
                            <th>Requestor</th>
                            <th>Date</th>
                            <th>Total Items</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingTasks as $task)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $task->requisition->requestedBy->name ?? 'N/A' }}</td>
                                <td>{{ $task->requisition->requisition_date->format('d-M-Y') }}</td>
                                <td class="text-center">{{ $task->requisition->items->count() }}</td>
                                <td class="text-center">
                                    {{-- View Details --}}
                                    <a href="{{ route('requisitions.show', $task->requisition) }}" class="btn btn-info btn-sm" title="View"><i class="fa fa-eye"></i></a>

                                    {{-- Edit / Adjust Amount (Only if status is pending) --}}
                                    {{-- Assuming you will create an edit route that allows approvers to modify amounts --}}
                                    <a href="{{ route('requisitions.edit', $task->requisition) }}" class="btn btn-warning btn-sm" title="Adjust Amount">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    {{-- Approve Button --}}
                                    <button class="btn btn-success btn-sm approve-btn" data-action="{{ route('requisitions.approve', $task->requisition) }}" title="Approve">
                                        <i class="fa fa-check"></i>
                                    </button>

                                    {{-- Reject Button --}}
                                    <button class="btn btn-danger btn-sm reject-btn" data-action="{{ route('requisitions.reject', $task->requisition) }}" title="Reject">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Hidden form for actions --}}
    <form id="approval-form" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="comments" id="swal-comments">
    </form>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#pending-req-table').DataTable();

        var approvalForm = $('#approval-form');

        // Approve Logic
        $('.approve-btn').click(function() {
            var url = $(this).data('action');
            Swal.fire({
                title: 'Approve Requisition?',
                text: "Are you sure you want to approve this request?",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve'
            }).then((result) => {
                if(result.isConfirmed) {
                    approvalForm.attr('action', url).submit();
                }
            });
        });

        // Reject Logic
        $('.reject-btn').click(function() {
            var url = $(this).data('action');
            Swal.fire({
                title: 'Reject Requisition?',
                text: "Please provide a reason:",
                input: 'textarea',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                confirmButtonColor: '#d33',
                preConfirm: (comment) => {
                    if (!comment) Swal.showValidationMessage('Reason is required');
                    return comment;
                }
            }).then((result) => {
                if(result.isConfirmed) {
                    $('#swal-comments').val(result.value);
                    approvalForm.attr('action', url).submit();
                }
            });
        });
    });
</script>
@endpush
