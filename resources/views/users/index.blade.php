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
                        <li class="breadcrumb-item active">
                            {{ __('breadcrumb.list') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-12">
                <!-- Filters -->
                <div class="card card-primary">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">{{ __('menupermission.filter') }}</h3>
                            <div>
                                <a href="{{ route('users.create') }}" class="btn btn-sm btn-success"
                                    title="{{ __('buttons.create') }}">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <form>
                        @csrf
                        <div class="card-body">
                            <div class="form-group row">
                                <label for="role" class="col-sm-1 col-form-label">{{ __('menupermission.role') }}
                                    <span style="color: red;">*</span></label>
                                <div class="col-sm-6">
                                    <select class="js-example-basic-single custom-select form-control-border" id="role"
                                        name="role">
                                        <!-- Default option -->
                                        <option value="0">--{{ __('dropdown.default_select') }}--</option>
                                    </select>
                                </div>
                                <div class="col-sm-5">
                                    <button class="btn btn btn-primary" type="button" id="getusersbyrole"
                                        name="getusersbyrole"><i class="fas fa-search"></i>
                                        {{ __('buttons.search') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <!-- Filters -->
            <div class="card card-primary" id="partial">

            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
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

            // Load Partial Data on Click
            $('#getusersbyrole').click(function(e) {

                var roleValue = $('#role').val();
                if (roleValue == 0) {
                    Swal.fire({
                        icon: "error",
                        title: "{{ __('sweetalert.error') }}",
                        text: "{{ __('sweetalert.required') }}",
                        showCloseButton: true,
                    });
                    return;
                }

                // Send AJAX request with selected values
                $.ajax({
                    url: "{{ route('getallusersbyroleblade') }}",
                    type: 'POST',

                    data: {
                        role_id: roleValue
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(data) {
                        // Implement partial page view later
                        $('#partial').empty();
                        $('#partial').append(data);
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
        });
    </script>
@endpush
