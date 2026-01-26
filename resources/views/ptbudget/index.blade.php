@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('budget.budget_management') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.list') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-coins mr-1"></i> {{ __('budget.budget_list') }}
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('ptbudgets.create') }}" class="btn btn-success btn-sm" title="{{ __('buttons.create') }}">
                                <i class="fas fa-plus"></i> {{ __('budget.define_new_budget') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="budget-table" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%" class="text-center">SL</th>
                                    <th style="width: 10%">{{ __('budget.fiscal_year') }}</th>
                                    <th style="width: 15%">{{ __('budget.purchase_type') }}</th>
                                    <th style="width: 15%" class="text-right">{{ __('budget.allocated_taka') }}</th>
                                    <th style="width: 15%" class="text-right">{{ __('budget.spent_taka') }}</th>
                                    <th style="width: 15%" class="text-right">{{ __('budget.remaining_taka') }}</th>
                                    <th style="width: 15%">{{ __('budget.remarks') }}</th>
                                    <th style="width: 10%" class="text-center">{{ __('budget.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $budget)
                                    @php
                                        $remaining = $budget->amount - $budget->spent_amount;
                                        // Highlight red if negative (over budget), green if positive
                                        $remainingClass = $remaining < 0 ? 'text-danger' : 'text-success';
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark" style="font-size: 0.9rem;">
                                                {{ $budget->fiscalYear->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $budget->category->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($budget->amount, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold text-muted">
                                            {{ number_format($budget->spent_amount, 2) }}
                                        </td>
                                        <td class="text-right font-weight-bold {{ $remainingClass }}">
                                            {{ number_format($remaining, 2) }}
                                        </td>
                                        <td>{{ $budget->remarks ?? '-' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('budgets.edit', $budget->id) }}" class="btn btn-warning btn-sm" title="{{ __('buttons.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $budget->id }}" title="{{ __('buttons.delete') }}">
                                                <i class="fas fa-trash"></i>
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
<script>
    $(document).ready(function() {
        var table = $('#budget-table').DataTable({
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                { "orderable": false, "targets": [7] } // Disable sorting on Action column (Index 7)
            ]
        });

        // 1. Handle Flash Messages
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        // 2. Handle Delete
        $('#budget-table').on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var row = table.row($(this).parents('tr'));
            var url = "{{ route('budgets.destroy', ':id') }}".replace(':id', id);

            Swal.fire({
                title: "{{ __('sweetalert.confirmation') }}",
                text: "{{ __('sweetalert.delete_revert_text') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                cancelButtonText: "{{ __('sweetalert.cancel') }}",
                confirmButtonText: "{{ __('sweetalert.delete_notice') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            toastr.success(res.message);
                            row.remove().draw(false);
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : "{{ __('sweetalert.toastr_delete') }}";
                            toastr.error(msg);
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
