@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.pending_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('lot.pending_title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('lot.pending_list') }}</h3>
                    </div>
                    <div class="card-body">
                        <table id="pending-lots-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('lot.table_sl') }}</th>
                                    <th>{{ __('lot.table_lot_number') }}</th>
                                    <th>{{ __('lot.purchase_type') }}</th>
                                    <th>{{ __('lot.table_submitted_by') }}</th>
                                    <th>{{ __('lot.table_entry_date') }}</th>
                                    <th>{{ __('lot.table_status') }}</th>
                                    <th>{{ __('lot.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- We loop over $pendingLots (Collection of Lot) --}}
                                @foreach ($pendingLots as $lot)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $lot->lot_number }}</td>
                                        <td>{{ $lot->purchaseType->name ?? 'N/A' }}</td>
                                        {{-- Find the first 'pending' task to see who submitted it --}}
                                        <td>{{ $lot->approvalDetails->first()->fromUser->name ?? 'N/A' }}</td>
                                        <td>{{ $lot->entry_date->format('d-M-Y') }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">{{ ucfirst($lot->status) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('lots.show', $lot) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            {{-- New Approve Button --}}
                                            <button class="btn btn-success btn-sm approve-btn" 
                                                    data-action="{{ route('lots.approve', $lot) }}"
                                                    title="Approve">
                                                <i class="fa fa-check"></i>
                                            </button>
                                             {{-- New Reject Button --}}
                                            <button class="btn btn-danger btn-sm reject-btn" 
                                                    data-action="{{ route('lots.reject', $lot) }}"
                                                    title="Reject">
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
        </div>
    </div>
@endsection

@push('js')
    {{-- This hidden form will be used by our JS to submit --}}
    <form id="approval-form" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="comments" id="swal-comments">
    </form>

    <script>
        $(document).ready(function() {
            $('#pending-lots-table').DataTable();

            var approvalForm = $('#approval-form');

            // APPROVE Button Click
            $('#pending-lots-table').on('click', '.approve-btn', function(e) {
                e.preventDefault();
                var actionUrl = $(this).data('action');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to approve this lot? This action cannot be undone.",
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, approve it!',
                    cancelButtonText: "{{ __('sweetalert.cancel') }}",
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#swal-comments').val(''); // Ensure comments are empty for approval
                        approvalForm.attr('action', actionUrl);
                        approvalForm.submit();
                    }
                });
            });

            // REJECT Button Click
            $('#pending-lots-table').on('click', '.reject-btn', function(e) {
                e.preventDefault();
                var actionUrl = $(this).data('action');

                Swal.fire({
                    title: 'Are you sure you want to reject?',
                    text: 'Please provide a reason for rejection (required):',
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Type your reason here...',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reject it!',
                    cancelButtonText: "{{ __('sweetalert.cancel') }}",
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    preConfirm: (comments) => {
                        if (!comments) {
                            Swal.showValidationMessage('A reason for rejection is required.')
                        }
                        return comments
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#swal-comments').val(result.value); // Add comments to hidden form
                        approvalForm.attr('action', actionUrl);
                        approvalForm.submit();
                    }
                });
            });
        });
    </script>
@endpush