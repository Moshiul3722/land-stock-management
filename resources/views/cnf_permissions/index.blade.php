@extends('layouts.app')

@section('content')
    <section class="content-header list-page">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('menupermission.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('menupermission.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ __('menupermission.title') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content list-page px-3">

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-12">
                <!-- Filters -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('menupermission.filter') }}</h3>
                    </div>
                    <form>
                        @csrf
                        <div class="card-body">
                            <div class="form-group row align-items-center mb-0">
                                <label for="role" class="col-12 col-md-auto col-form-label">{{ __('menupermission.role') }}
                                    <span style="color: red;">*</span></label>
                                <div class="col-12 col-sm-8 col-md-6 mb-2 mb-sm-0">
                                    <select class="js-example-basic-single custom-select form-control-border" id="role"
                                        name="role">
                                        <!-- Default option -->
                                        <option value="0">--{{ __('dropdown.default_select') }}--</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-4 col-md-auto">
                                    <button class="btn btn btn-primary permission-search-btn" type="button" id="getPermissionButton"
                                        name="getPermissionButton"><i class="fas fa-search"></i>
                                        {{ __('buttons.search') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Same padded container as the filter card, so both cards are equally wide --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary" id="partial">

                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_css')
    <style>
        /* Role-wise menu permission table */
        #permission_table {
            font-size: 1rem; /* follows the site base size: 17px desktop, 16px tablet, 15px phone */
            margin-bottom: 1rem;
        }

        #permission_table th,
        #permission_table td {
            padding: 0.3rem 0.6rem;
            vertical-align: middle;
            line-height: 1.35;
        }

        #permission_table thead th {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            text-align: center;
            white-space: nowrap;
        }

        #permission_table .permission-cell {
            width: 110px;
            text-align: center;
        }

        /* Larger checkbox with a generous click area */
        #permission_table .permission-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.5rem;
            min-height: 1.6rem;
            margin: 0;
            cursor: pointer;
        }

        #permission_table .permission-checkbox {
            width: 1.6rem;
            height: 1.6rem;
            margin: 0;
            cursor: pointer;
            accent-color: #3f51b5;
        }

        #permission_table .permission-checkbox:focus-visible {
            outline: 3px solid rgba(63, 81, 181, 0.45);
            outline-offset: 2px;
        }

        @media (max-width: 575.98px) {
            #permission_table th,
            #permission_table td {
                padding: 0.3rem 0.35rem;
            }

            #permission_table thead th {
                white-space: normal;
            }

            #permission_table .permission-cell {
                width: 64px;
            }

            #permission_table td {
                overflow-wrap: anywhere;
            }

            .permission-search-btn,
            #save_permissions {
                width: 100%;
                min-height: 44px;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            // Getting the list of Bank Districts
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
                    // Open with the signed-in user's own role chosen and its permissions shown
                    var ownRole = @json((string) (auth()->user()->role_id ?? ''));
                    if (ownRole !== '' && dropdown.find('option[value="' + ownRole + '"]').length) {
                        dropdown.val(ownRole).trigger('change');
                        $('#getPermissionButton').trigger('click');
                    }
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
            $('#getPermissionButton').click(function(e) {
                // e.preventDefault();

                // Get the selected district and bank values
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
                    url: "{{ route('getallmenusbyrole') }}",
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
                            text: "{{ __('sweetalert.required') }}",
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
