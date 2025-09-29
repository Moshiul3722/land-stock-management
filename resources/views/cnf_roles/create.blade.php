@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('rolemanagement.title') }}</h1>
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
                    <h3 class="card-title">{{ __('rolemanagement.create_new_role') }}</h3>
                </div>
                <form id="create_role_form" name="create_role_form" action={{ route('cnfRoles.store') }} method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_name_en">{{ __('rolemanagement.role_name_en') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="text" name="role_name_en" id="role_name_en"
                                        class="form-control @error('role_name_en') is-invalid @enderror">
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
                                        class="form-control @error('role_name_bn') is-invalid @enderror">
                                    <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card-footer clearfix">
                        <div class="float-left">
                            <a class="btn btn-success" href={{ route('cnfRoles.index') }}>
                                <i class="fas fa-arrow-left"></i>
                                {{ __('buttons.back_to_list') }}
                            </a>
                        </div>
                        <div class="float-right">
                            <button type="submit" class="btn btn-primary" id="role_save" name="role_save">
                                <i class="fas fa-save"></i>
                                {{ __('buttons.save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('#role_save').click(function(event) {
                event.preventDefault();
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
                    text: "{{ __('sweetalert.about_to_save') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('sweetalert.save_notice') }}",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Hide all elements with class .invalid-feedback
                        $('.invalid-feedback').css('visibility', 'hidden');
                        $.ajax({
                            url: "{{ route('cnfRoles.store') }}",
                            type: 'POST',
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
                                    title: "{{ __('sweetalert.save_complete') }}",
                                    text: "{{ __('sweetalert.saved') }}",
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
                                        title: '{{ __("sweetalert.error") }}',
                                        text: '{{ __("sweetalert.error_title") }}',
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
