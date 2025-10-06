@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('user.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href={{ route('users.index') }}>{{ __('breadcrumb.list') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ __('breadcrumb.create') }}
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
                    <h3 class="card-title">{{ __('user.create_new_user') }}</h3>
                </div>
                <form id="create_user_form" name="create_user_form" action={{ route('users.store') }} method="POST"
                    autocomplete="off">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('user.user_name_en') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="text" name="name" id="name"
                                        class="form-control @error('name') is-invalid @enderror" autocomplete="off">
                                    <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_bn">{{ __('user.user_name_bn') }}</label>
                                    <input type="text" name="name_bn" id="name_bn"
                                        class="form-control @error('name_bn') is-invalid @enderror" autocomplete="off">
                                    <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">{{ __('user.email') }} <span style="color: red;">*</span></label>
                                    <input type="email" name="email" id="email"
                                        class="form-control @error('email') is-invalid @enderror">
                                    <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">{{ __('user.role') }} <span style="color: red;">*</span></label>
                                    <select class="js-example-basic-single custom-select form-control-border" id="role"
                                        name="role">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">{{ __('user.password') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" autocomplete="off">
                                    <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password">{{ __('user.confirm_password') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                        class="form-control @error('confirm_password') is-invalid @enderror"
                                        autocomplete="off">
                                    <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card-footer clearfix">
                        <div class="float-left">
                            <a class="btn btn-success" href={{ route('users.index') }}>
                                <i class="fas fa-arrow-left"></i>
                                {{ __('buttons.back_to_list') }}
                            </a>
                        </div>
                        <div class="float-right">
                            <button type="submit" class="btn btn-primary" id="user_save" name="user_save">
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
            $('#user_save').click(function(event) {
                event.preventDefault();
                // Get the values
                var userNameEn = $('#name').val();
                var userNameBn = $('#name_bn').val();
                var email = $('#email').val();
                var role = $('#role').val();
                var district = $('#districts').val();
                var password = $('#password').val();
                var confirm_password = $('#confirm_password').val();


                //Validations
                if (userNameEn == undefined || userNameEn == "" ||
                    email == undefined || email == "" ||
                    password == undefined || password == "" ||
                    confirm_password == undefined || confirm_password == "" ||
                    role == undefined || role == 0) {
                    Swal.fire({
                        title: "{{ __('sweetalert.error') }}",
                        text: "{{ __('sweetalert.required') }}",
                        icon: 'warning'
                    });
                    return;
                }

                // Password Confirmation Validation
                if (password != confirm_password) {
                    Swal.fire({
                        title: "{{ __('sweetalert.error') }}",
                        text: "{{ __('user.password_match') }}",
                        icon: 'warning'
                    });
                    return;
                }

                // Email Validation
                if (!emailRegex.test(email)) {
                    Swal.fire({
                        title: "{{ __('sweetalert.error') }}",
                        text: "{{ __('sweetalert.not_valid_email') }}",
                        icon: 'warning'
                    });
                    return;
                }

                // Email Validation
                if (role == 2 || role == 3) {
                    if (district == 0) {
                        Swal.fire({
                            title: "{{ __('sweetalert.error') }}",
                            text: "District Must Be Selected!",
                            icon: 'warning'
                        });
                        return;
                    }
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
                            url: "{{ route('users.store') }}",
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                name: userNameEn,
                                name_bn: userNameBn,
                                email: email,
                                role_id: role,
                                district_id: district,
                                password: password,
                            },
                            beforeSend: function() {
                                $('#wait').show();
                            },
                            success: function(data) {
                                $('#wait').hide();
                                Swal.fire({
                                    icon: "success",
                                    title: "{{ __('sweetalert.save_complete') }}",
                                    text: "{{ __('sweetalert.saved') }}",
                                }).then((result) => {
                                    $('#wait').show();
                                    window.location.href =
                                        "{{ route('users.index') }}";
                                });
                            },
                            error: function(xhr, status, error) {
                                $('#wait').hide();
                                if (xhr.status === 422) {
                                    console.log(xhr.responseJSON.errors);
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
                                        text: "{{ __('sweetalert.error_title') }}",
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

            // Getting the list of all roles
            $.ajax({
                url: "{{ route('getAllRoles') }}",
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    $('#wait').show();
                },
                success: function(data) {
                    var dropdown = $('#role');
                    dropdown.empty();
                    dropdown.append($('<option></option>').attr('value', '0').text(
                        "--{{ __('dropdown.default_select') }}--"));
                    $.each(data.original, function(key, value) {
                        dropdown.append($('<option></option>').attr('value', value.id).text(
                            selectedLocale === 'en' ? value.role_name_en : value
                            .role_name_bn));
                    });
                },
                error: function(err) {
                    $('#wait').hide();
                    // Display Sweet Alert for error
                    Swal.fire({
                        icon: 'error',
                        title: "{{ __('sweetalert.error') }}",
                        text: "{{ __('sweetalert.error_title') }}",
                        showCloseButton: true,
                    });
                },
                complete: function() {
                    $('#wait').hide();
                }
            });
        });
    </script>
@endpush
