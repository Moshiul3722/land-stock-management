@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('user.edit_user') }}</h1>
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
                            {{ __('breadcrumb.edit') }}
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
                    <h3 class="card-title">{{ __('user.edit_user') }}</h3>
                    <form id="edit_role_form" name="edit_role_form"
                        action={{ route('users.update', ['user' => $user->id]) }} method="POST">
                        @csrf
                        @method('PATCH')
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">{{ __('user.user_name_en') }} <span
                                        style="color: red;">*</span></label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" autocomplete="off"
                                    value="{{ $user->name }}">
                                <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_bn">{{ __('user.user_name_bn') }}</label>
                                <input type="text" name="name_bn" id="name_bn"
                                    class="form-control @error('name_bn') is-invalid @enderror" autocomplete="off"
                                    value="{{ $user->name_bn }}">
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
                                    class="form-control @error('email') is-invalid @enderror" value="{{ $user->email }}">
                                <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="role">{{ __('user.role') }} <span style="color: red;">*</span></label>
                                <select class="js-example-basic-single custom-select form-control-border" id="role"
                                    name="role">
                                </select>
                            </div>
                        </div>
                        {{-- <div class="col-sm-6">
                            <div class="form-group">
                                <label for="type">{{ __('user.category') }} <span style="color: red;">*</span></label>
                                <select class="js-example-basic-single custom-select form-control-border" id="type"
                                    name="type">
                                </select>
                            </div>
                        </div> --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                {!! Form::label('department_id', 'Section/Department:') !!}
                                <select name="department_id" class="form-control select2">
                                    <option value="">Select Department (Optional)</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ $user->department_id == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">{{ __('user.reset_password') }}</label>
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" autocomplete="off">
                                <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirm_password">{{ __('user.reset_password_confirm') }}</label>
                                <input type="password" name="confirm_password" id="confirm_password"
                                    class="form-control @error('confirm_password') is-invalid @enderror" autocomplete="off">
                                <span class="invalid-feedback" role="alert" style="visibility: hidden;">
                                    <strong></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Update Button -->
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            <button type="button" class="btn btn-success" id="user_update" name="user_update">
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
            var role_id = "{{ $user->role_id }}";
            var type_id = "{{ $user->usertype_id }}";
            var district_id = "{{ $user->district_id }}";

            $(document).ready(function() {
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
                                .role_name_bn).prop('selected', value.id == role_id));
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

                // Getting the list of all user types
                $.ajax({
                    url: "{{ route('getallusertypes') }}",
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(data) {
                        var dropdown = $('#type');
                        dropdown.empty();
                        dropdown.append($('<option></option>').attr('value', '0').text(
                            "--{{ __('dropdown.default_select') }}--"));
                        $.each(data.original, function(key, value) {
                            dropdown.append($('<option></option>').attr('value', value.id).text(
                                selectedLocale === 'en' ? value.name_en : value
                                .name_bn).prop('selected', value.id == type_id));
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

                $('#user_update').click(function(event) {
                    event.preventDefault();
                    // Get the values
                    var userNameEn = $('#name').val();
                    var userNameBn = $('#name_bn').val();
                    var email = $('#email').val();
                    var role = $('#role').val();
                    var password = $('#password').val();
                    var confirm_password = $('#confirm_password').val();
                    var department_id = $('#department_id').val();


                    //Validations
                    if (userNameEn == undefined || userNameEn == "" ||
                        email == undefined || email == "" ||
                        role == undefined || role == 0) {
                        Swal.fire({
                            title: "{{ __('sweetalert.error') }}",
                            text: "{{ __('sweetalert.required') }}",
                            icon: 'warning'
                        });
                        return;
                    }
                    // Construct the data object
                    var requestData = {
                        name: userNameEn,
                        name_bn: userNameBn,
                        email: email,
                        role_id: role,
                        district_id: district_id,
                    };
                    // Password Confirmation Validation
                    if (password != "") {
                        if (confirm_password != "") {
                            if (password != confirm_password) {
                                Swal.fire({
                                    title: "{{ __('sweetalert.error') }}",
                                    text: "{{ __('user.password_match') }}",
                                    icon: 'warning'
                                });
                                return;
                            } else {
                                // Add password to requestData
                                requestData.password = password;
                            }
                        } else {
                            // Confirm Password Field Empty
                            Swal.fire({
                                title: "{{ __('sweetalert.error') }}",
                                text: "{{ __('user.confirm_password_empty') }}",
                                icon: 'warning'
                            });
                            return;
                        }
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

                    // Department Validation
                    // Department must be required if role id is 5
                    if (role == 5 && (department_id == undefined || department_id == "")) {
                        Swal.fire({
                            title: "{{ __('sweetalert.error') }}",
                            text: "Please select department for the selected role.",
                            icon: 'warning'
                        });
                        return;
                    }

                    // TODO : Update Functionality
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
                                url: "{{ route('users.update', ['user' => $user->id]) }}",
                                type: 'PUT',
                                dataType: 'json',
                                data: requestData,
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
            });
        </script>
    @endpush
