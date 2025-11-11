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
                        <h3 class="card-title">{{ __('menupermission.filter') }}</h3>
                        {{-- Add button moved to the list card below --}}
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="status_filter" class="col-sm-2 col-form-label">{{ __('lot.table_status') }}</label>
                            <div class="col-sm-6">
                                <select class="form-control" id="status_filter" name="status_filter">
                                    <option value="">All</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <button class="btn btn-primary" type="button" id="filter-btn">
                                    <i class="fas fa-search"></i> {{ __('buttons.search') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">{{ __('lot.list_title') }}</h3>
                            <a href="{{ route('lots.create') }}" class="btn btn-sm btn-success ms-auto"
                                title="{{ __('buttons.create') }}">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body" id="lot-list-container">
                        {{-- Partial view will be loaded here --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Function to load lots
            function loadLots(status = '') {
                const url = "{{ route('lots.index') }}";
                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        status: status
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(response) {
                        $('#wait').hide();
                        $('#lot-list-container').html(response);
                        $('#lots-table').DataTable({
                            "columnDefs": [
                                // SL
                                {
                                    "width": "10%",
                                    "targets": 0,
                                    "className": "text-center"
                                },
                                // Lot Number
                                {
                                    "width": "20%",
                                    "targets": 1
                                },
                                // Purchase Type
                                {
                                    "width": "20%",
                                    "targets": 2
                                },
                                // Entry Date
                                {
                                    "width": "20%",
                                    "targets": 3
                                },
                                // Status
                                {
                                    "width": "10%",
                                    "targets": 4,
                                    "className": "text-center"
                                },
                                // Action
                                {
                                    "width": "20%",
                                    "targets": 5,
                                    "className": "text-center"
                                }
                            ]
                        });
                    },
                    error: function(xhr) {
                        $('#wait').hide();
                        toastr.error('Failed to load lots.');
                    }
                });
            }

            // Initial load
            loadLots();

            // Filter button click event
            $('#filter-btn').on('click', function() {
                var status = $('#status_filter').val();
                loadLots(status);
            });

            // Delete functionality using event delegation
            $('#lot-list-container').on('click', '.delete-btn', function() {
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
                                toastr.success("{{ __('lot.toastr_delete') }}");
                                // Reload the table with the current filter
                                $('#filter-btn').click();
                            },
                            error: function(xhr) {
                                $('#wait').hide();
                                toastr.error("{{ __('sweetalert.toastr_error') }}");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
