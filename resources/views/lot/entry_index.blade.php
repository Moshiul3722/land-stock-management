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
                                    <th>{{ __('lot.table_submitted_by') }}</th>
                                    <th>{{ __('lot.table_entry_date') }}</th>
                                    <th>{{ __('lot.table_status') }}</th>
                                    <th>{{ __('lot.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingLotTasks as $task)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $task->lot->lot_number }}</td>
                                        <td>{{ $task->fromUser->name ?? 'N/A' }}</td>
                                        <td>{{ $task->lot->entry_date->format('d-M-Y') }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">{{ ucfirst($task->status) }}</span>
                                        </td>
                                        <td class="text-center">
                                            {{-- These routes will need to be created --}}
                                            <a href="{{ route('lots.show', $task->lot) }}" class="btn btn-info btn-sm" title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="#" class="btn btn-success btn-sm" title="Approve">
                                                <i class="fa fa-check"></i>
                                            </a>
                                             <a href="#" class="btn btn-danger btn-sm" title="Reject">
                                                <i class="fa fa-times"></i>
                                            </a>
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
    <script>
        $(document).ready(function() {
            $('#pending-lots-table').DataTable();
        });
    </script>
@endpush