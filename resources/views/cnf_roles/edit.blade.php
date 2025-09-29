@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('rolemanagement.edit_role') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ __('rolemanagement.title') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="col-sm-12">
            <div class="clearfix"></div>
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ __('rolemanagement.edit_role') }}</h3>
                    <form id="edit_role_form" name="edit_role_form"
                        action={{ route('cnfRoles.update', ['cnfRole' => $cnfRole->id]) }} method="POST">
                        @csrf
                        @method('PATCH')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role_name_en">{{ __('rolemanagement.role_name_en') }} <span
                                        style="color: red;">*</span></label>
                                <input type="text" name="role_name_en" id="role_name_en"
                                    class="form-control @error('role_name_en') is-invalid @enderror"
                                    value="{{ $cnfRole->role_name_en }}">
                                <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role_name_bn">{{ __('rolemanagement.role_name_bn') }} <span
                                        style="color: red;">*</span></label>
                                <input type="text" name="role_name_bn" id="role_name_bn"
                                    class="form-control @error('role_name_bn') is-invalid @enderror"
                                    value="{{ $cnfRole->role_name_bn }}">
                                <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Update Button -->
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            <button type="button" class="btn btn-success" id="role_update" name="role_update">
                                <i class="fas fa-edit"></i>
                                {{ __('buttons.update') }}
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('js')
        <script>
            $(document).ready(function() {
                $('#role_update').click(function(event) {
                    // Get the role_id
                    var roleId = {{ $cnfRole->id }};

                    // Get the role_name
                    var roleNameEn = $('#role_name_en').val();
                    var roleNameBn = $('#role_name_bn').val();

                    if (roleNameEn == undefined || roleNameEn == "" || roleNameBn == undefined || roleNameBn ==
                        "") {
                        Swal.fire({
                            title: "{{ __('sweetalert.error') }}",
                            text: "{{ __('sweetalert.required') }}",
                            icon: 'warning'
                        });
                        return;
                    }
                    Swal.fire({
                        title: "{{ __('sweetalert.confirmation') }}",
                        text: "{{ __('sweetalert.about_to_update') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('sweetalert.update_notice') }}",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Hide all elements with class .invalid-feedback
                            $('.invalid-feedback').css('visibility', 'hidden');
                            $.ajax({
                                url: "{{ route('cnfRoles.update', ['cnfRole' => $cnfRole->id]) }}",
                                type: 'PATCH', // Use PATCH method for updates
                                dataType: 'json',
                                data: {
                                    role_name_en: roleNameEn,
                                    role_name_bn: roleNameBn
                                },
                                beforeSend: function() {
                                    $('#wait').show();
                                },
                                success: function(data) {
                                    $('#wait').hide();
                                    Swal.fire({
                                        icon: 'success',
                                        title: "{{ __('sweetalert.success') }}",
                                        text: "{{ __('sweetalert.update_complete') }}",
                                    }).then((result) => {
                                        $('#wait').show();
                                        window.location.href =
                                            "{{ route('cnfRoles.index') }}";
                                    });
                                },
                                error: function(xhr, status, error) {
                                    $('#wait').hide();
                                    if (xhr.status === 422) {
                                        // Validation errors
                                        $.each(xhr.responseJSON.errors, function(inputName,
                                            errorMessage) {
                                            var inputField = $('input[name="' +
                                                inputName + '"]');
                                            inputField.addClass('is-invalid');
                                            inputField.siblings('.invalid-feedback')
                                                .children('strong').text(
                                                    errorMessage);
                                            inputField.siblings('.invalid-feedback')
                                                .css('visibility', 'visible');
                                        });
                                    } else {
                                        // Other errors
                                        Swal.fire({
                                            icon: 'error',
                                            title: "{{ __('sweetalert.error') }}",
                                            text: "{{ __('sweetalert.update_failed') }}",
                                        });
                                    }
                                },
                                complete: function() {
                                    $('#wait').hide();
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
