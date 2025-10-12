@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('category.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <a href={{ route('categories.index') }}>{{ __('breadcrumb.list') }}</a>
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
                            <h3 class="card-title">{{ __('category.table_header') }}</h3>
                            <div>
                                <a href="{{ route('categories.create') }}" class="btn btn-sm btn-success"
                                    title="{{ __('buttons.create') }}">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="categories-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('category.table_sl') }}</th>
                                    <th>{{ __('category.table_name') }}</th>
                                    <th>{{ __('category.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr id="category-row-{{ $category->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            <a href="{{ route('categories.show', $category) }}" class="btn btn-info btn-sm"
                                                title="View"><i class="fa fa-eye"></i></a>
                                            <a href="{{ route('categories.edit', $category) }}"
                                                class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $category->id }}"
                                                title="Delete">
                                                <i class="fa fa-trash"></i>
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
            var table = $('#categories-table').DataTable({
                // Define column widths
                "columnDefs": [{
                        "width": "20%",
                        "targets": 0,
                        "className": "text-center"
                    }, // SL column
                    {
                        "width": "50%",
                        "targets": 1
                    }, // Name column
                    {
                        "width": "30%",
                        "targets": 2,
                        "className": "text-center"
                    } // Action column
                ]
            });

            // Handle delete button click event
            $('#categories-table').on('click', '.delete-btn', function(e) {
                e.preventDefault();
                var categoryId = $(this).data('id');
                var row = table.row($(this).parents('tr'));

                Swal.fire({
                    title: "{{ __('sweetalert.confirmation') }}",
                    text: "{{ __('sweetalert.delete_revert_text') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('sweetalert.delete_notice') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        const deleteUrlTemplate =
                            "{{ route('categories.destroy', ['category' => ':id']) }}";
                        const finalUrl = deleteUrlTemplate.replace(':id', categoryId);
                        $.ajax({
                            url: finalUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                toastr.success(
                                    "{{ __('category.toastr_delete') }}");
                                // Remove the row from the datatable
                                row.remove().draw(false);
                            },
                            error: function(xhr) {
                                toastr.error(
                                    "{{ __('sweetalert.toastr_delete') }}"
                                    );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
