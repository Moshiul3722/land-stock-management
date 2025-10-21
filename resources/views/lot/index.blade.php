@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active"><a href={{ route('lots.index') }}>{{ __('breadcrumb.list') }}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">{{ __('lot.list_title') }}</h3>
                            <a href="{{ route('lots.create') }}" class="btn btn-sm btn-success"
                                title="{{ __('buttons.create') }}">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="lots-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('lot.table_sl') }}</th>
                                    <th>{{ __('lot.table_lot_number') }}</th>
                                    <th>{{ __('lot.table_entry_date') }}</th>
                                    <th>{{ __('lot.table_status') }}</th>
                                    <th>{{ __('lot.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lots as $lot)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $lot->lot_number }}</td>
                                        <td>{{ $lot->entry_date->format('d-M-Y') }}</td>
                                        <td>
                                            {{-- Conditional Badge Classes --}}
                                            @php
                                                $badgeClass = 'bg-info'; // Default
                                                if ($lot->status === 'draft') {
                                                    $badgeClass = 'bg-secondary';
                                                } elseif ($lot->status === 'pending') {
                                                    $badgeClass = 'bg-warning text-dark';
                                                } elseif ($lot->status === 'approved') {
                                                    $badgeClass = 'bg-success';
                                                } elseif ($lot->status === 'rejected') {
                                                    $badgeClass = 'bg-danger';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ ucfirst($lot->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('lots.show', $lot) }}" class="btn btn-info btn-sm"
                                                title="View"><i class="fa fa-eye"></i></a>
                                            {{-- Conditionally show edit/delete only for drafts --}}
                                            @if ($lot->status === 'draft')
                                                <a href="{{ route('lots.edit', $lot) }}" class="btn btn-warning btn-sm"
                                                    title="Edit"><i class="fa fa-edit"></i></a>
                                                <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                    data-id="{{ $lot->id }}" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
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
            $('#lots-table').DataTable();

            $('#lots-table').on('click', '.delete-btn', function() {
                var lotId = $(this).data('id');
                const deleteUrlTemplate = "{{ route('lots.destroy', ['lot' => ':id']) }}";
                const finalUrl = deleteUrlTemplate.replace(':id', lotId);

                Swal.fire({
                    title: "{{ __('sweetalert.confirmation') }}",
                    text: "{{ __('sweetalert.delete_revert_text') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: "{{ __('sweetalert.cancel') }}",
                    confirmButtonText: "{{ __('sweetalert.delete_notice') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: finalUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            beforeSend: function() {
                                $('#wait').show();
                            },
                            success: function(response) {
                                $('#wait').hide();
                                toastr.success(
                                    "{{ __('lot.toastr_delete') }}");
                                location.reload();
                            },
                            error: function(xhr) {
                                $('#wait').hide();
                                toastr.error(
                                    "{{ __('sweetalert.toastr_error') }}"
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
