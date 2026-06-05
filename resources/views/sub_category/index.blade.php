@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('subcategory.table_header') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <a href={{ route('subcategories.index') }}>{{ __('breadcrumb.list') }}</a>
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
                            <h3 class="card-title">{{ __('subcategory.table_header') }}</h3>
                            <div>
                                <a href="{{ route('subcategories.create') }}" class="btn btn-sm btn-success"
                                    title="{{ __('buttons.create') }}">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="sub-categories-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('category.table_sl') }}</th>
                                    <th>{{ __('category.title') }}</th>
                                    <th>{{ __('subcategory.table_name') }}</th>
                                    {{-- <th>Code</th> --}}
                                    {{-- <th>{{ __('category.action') }}</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subCategories as $subCategory)
                                    <tr id="row-{{ $subCategory->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $subCategory->category->name ?? 'N/A' }}</td>
                                        <td>{{ $subCategory->name }}</td>
                                        {{-- <td>{{ $subCategory->code ?? '-' }}</td> --}}
                                        {{-- <td>
                                            <a href="{{ route('subcategories.edit', $subCategory->id) }}"
                                                class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                                            <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $subCategory->id }}"
                                                title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td> --}}
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
            var table = $('#sub-categories-table').DataTable({
                // Define column widths
                "columnDefs": [{
                        "width": "10%",
                        "targets": 0,
                        "className": "text-center"
                    }, // SL
                    {
                        "width": "45%",
                        "targets": 1
                    }, // Parent Category
                    {
                        "width": "45%",
                        "targets": 2
                    }, // Sub Category Name
                ]
            });

            // Handle delete button click event
            $('#sub-categories-table').on('click', '.delete-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var row = table.row($(this).parents('tr'));

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
                        const deleteUrlTemplate = "{{ route('subcategories.destroy', ':id') }}";
                        const finalUrl = deleteUrlTemplate.replace(':id', id);
                        
                        $.ajax({
                            url: finalUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                toastr.success("Sub Category deleted successfully.");
                                // Remove the row from the datatable
                                row.remove().draw(false);
                            },
                            error: function(xhr) {
                                toastr.error("{{ __('sweetalert.toastr_delete') }}");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush