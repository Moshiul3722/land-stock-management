<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/bootstrap4-toggle.min.css') }}" />

    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}"/>

    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('css/icheck-bootstrap.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" />


    <link href="{{ asset('css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/keyTable.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.bootstrap4.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- For Wait Loader -->
    <link rel="stylesheet" href="{{ asset('css/sms.css') }}" />

    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            height: calc(2.25rem + 2px);
        }

        .toggle.ios,
        .toggle-on.ios,
        .toggle-off.ios {
            border-radius: 20rem;
        }

        .toggle.ios .toggle-handle {
            border-radius: 20rem;
        }

        .ttm {
            font-size: 2px;
        }

        .dt-search {
            margin-bottom: 5%;
        }

        /* Custom switch button */
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .custom-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: green;
            transition: .4s;
            border-radius: 40px;
        }

        .custom-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-image: url("{{ asset('img/bg.png') }}");
            background-size: cover; /* Ensures the image covers the entire element */
            background-position: center; /* Centers the image */
            background-repeat: no-repeat; /* Prevents the image from repeating */
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .custom-slider {
            background-color: #1a4b96;
        }

        input:checked + .custom-slider:before {
            background-image: url("{{ asset('img/red.png') }}");
            background-size: cover; /* Ensures the image covers the entire element */
            background-position: center; /* Centers the image */
            background-repeat: no-repeat; /* Prevents the image from repeating */
        }

        input:checked + .custom-slider:before {
            transform: translateX(26px);
        }

        .custom-slider:after {
            content: attr(data-off);
            color: white;
            display: block;
            position: absolute;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 75%;
            font-size: 10px;
        }

        input:checked + .custom-slider:after {
            left: 25%;
            content: attr(data-on);
        }

        .plus-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1px;
            margin-bottom: auto;
            margin-left: auto;
            margin-right: auto;
            background-color: #28a745; /* bg-success color */
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%; /* rounded-circle effect */
            box-shadow: none;
            color: white;
        }
    </style>
    @stack('third_party_stylesheets')

    @stack('page_css')
</head>

<body class="hold-transition layout-fixed">
    <div id="wait"></div>
    <div class="wrapper" >
        <!-- Main Header -->
        <nav class=" navbar navbar-expand ">
            <a href="{{ url('/home') }}" class="brand-link">
                <img src="{{ asset('theme/assets/img/dshe_logo.png') }}"
                     alt="{{ config('app.name') }} Logo"
                     class="brand-image">
                <span style="text-align: center; position: absolute; left: 50%; transform: translateX(-50%);" class="brand-text font-weight-bold small-size" > Scholarship Management System</span>
            </a>
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <label class="custom-switch">
                        <input type="checkbox" id="languageToggle" {{ app()->getLocale() === 'en' ? 'checked' : '' }}>
                        <span class="custom-slider" data-on="EN" data-off="BN" data-offstyle="success"></span>
                    </label>
                </li>
            </ul>
        </nav>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" style="margin-left: 0;">
            <section class="content">
                @yield('content')
            </section>
        </div>

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>{{ __('footer.version') }}</b> v1.0
            </div>
            <strong>
                {{ __('footer.copyright') }} &copy; <?php echo date('Y'); ?> Dhaka Sanitation Improvement Project (DSIP)
            </strong>
            {{ __('footer.reserved') }}
        </footer>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script src="{{ asset('js/popper.min.js') }}"></script>

    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('js/bs-custom-file-input.min.js') }}"></script>

    <!-- AdminLTE App -->
    <script src="{{ asset('js/adminlte.min.js') }}"></script>

    <script src="{{ asset('js/moment.min.js') }}"></script>

    <script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>

    <script src="{{ asset('js/bootstrap4-toggle.min.js') }}"></script>

    <script src="{{ asset('js/select2.ajax.min.js') }}"></script>

    <script src="{{ asset('js/bootstrapSwitch.min.js') }}"></script>

    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>

    <script src="{{ asset('js/jszip.min.js') }}"></script>
    <script src="{{ asset('js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('js/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/responsive.bootstrap4.js') }}"></script>

    <script>
        $(function() {
            bsCustomFileInput.init();
        });
        // For Localization
        var selectedLocale = "{{ app()->getLocale() === 'en' ? 'en' : 'bn' }}";

        // For Email validation
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Select2
        $('.js-example-basic-single').select2({
            placeholder: 'Select an option'
        });

        $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
            $('#languageToggle').change(function() {
                var locale = $(this).prop('checked') ? 'en' : 'bn';
                var url = "{{ route('change.language', ['locale' => ':locale']) }}";
                // Show loader
                $('#wait').show();
                window.location.href = url.replace(':locale', locale);
            });
        });
    </script>

    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>

    <script>
        // Get the CSRF token value from the meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Set the CSRF token in the headers of all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    </script>
    @stack('third_party_scripts')

    @stack('page_scripts')
    @stack('js')
</body>

</html>
