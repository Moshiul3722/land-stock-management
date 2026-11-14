<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>

    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App Meta Tags (Private) -->
    <meta name="author" content="UltraCore Creatives Ltd. for Ministry of Land">
    <meta name="description" content="Inventory Management System for Ministry of Land.">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#343a40">
    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/bootstrap4-toggle.min.css') }}" />

    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}" />

    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('css/icheck-bootstrap.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" />

    {{--
    <!-- DataTable 2.0.5 -->
    <link href="https://cdn.datatables.net/2.0.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/5.0.0/css/fixedColumns.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/keytable/2.12.0/css/keyTable.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap4.min.css" rel="stylesheet">
    --}}

    <link href="{{ asset('css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
    {{-- <link href="{{ asset('css/fixedColumns.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fixedHeader.bootstrap4.min.css') }}" rel="stylesheet"> --}}
    <link href="{{ asset('css/keyTable.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/responsive.bootstrap4.min.css') }}" rel="stylesheet">

    {{-- For Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- For Wait Loader & Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/land.css') }}" />
    <!-- Select2 -->
    {{-- <link href="{{ asset('css/select2npm.min.css') }}" rel="stylesheet" /> --}}




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
            background-size: cover;
            /* Ensures the image covers the entire element */
            background-position: center;
            /* Centers the image */
            background-repeat: no-repeat;
            /* Prevents the image from repeating */
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.custom-slider {
            background-color: #1a4b96;
        }

        input:checked+.custom-slider:before {
            background-image: url("{{ asset('img/red.png') }}");
            background-size: cover;
            /* Ensures the image covers the entire element */
            background-position: center;
            /* Centers the image */
            background-repeat: no-repeat;
            /* Prevents the image from repeating */
        }


        input:checked+.custom-slider:before {
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

        input:checked+.custom-slider:after {
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
            background-color: #28a745;
            /* bg-success color */
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            /* rounded-circle effect */
            box-shadow: none;
            color: white;
        }
    </style>
    {{-- Sonner-style toast notifications (window.appToast) --}}
    <link rel="stylesheet" href="{{ asset('css/app-toast.css') }}?v={{ @filemtime(public_path('css/app-toast.css')) }}">

    @stack('third_party_stylesheets')

    @stack('page_css')
    {{-- One look for every dialog (matches the lot entry modal) --}}
    <link rel="stylesheet" href="{{ asset('css/app-modal.css') }}?v={{ @filemtime(public_path('css/app-modal.css')) }}">
    {{-- One look for every list page / table (matches the requisition list) --}}
    <link rel="stylesheet" href="{{ asset('css/app-list.css') }}?v={{ @filemtime(public_path('css/app-list.css')) }}">
    {{-- Top bar: section / department name and today's date --}}
    <link rel="stylesheet" href="{{ asset('css/app-topbar.css') }}?v={{ @filemtime(public_path('css/app-topbar.css')) }}">
    {{-- Left sidebar colours --}}
    <link rel="stylesheet" href="{{ asset('css/app-sidebar.css') }}?v={{ @filemtime(public_path('css/app-sidebar.css')) }}">
    {{-- Site-wide responsive base font size (17px desktop, 16px tablet, 15px phone) --}}
    <link rel="stylesheet" href="{{ asset('css/base-font.css') }}?v={{ @filemtime(public_path('css/base-font.css')) }}">

    {{--
        Libraries live in <head> so they are loaded once: Livewire (wire:navigate) replaces
        only the <body> when the page changes and runs the page's inline scripts again.
    --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    {{-- Keeps jQuery pages working when Livewire changes the page without a reload --}}
    <script src="{{ asset('js/app-navigate.js') }}?v={{ @filemtime(public_path('js/app-navigate.js')) }}"></script>

    <script src="{{ asset('js/popper.min.js') }}"></script>

    <script src="{{ asset('js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('js/bs-custom-file-input.min.js') }}"></script>

    <!-- AdminLTE App -->
    <script src="{{ asset('js/adminlte.min.js') }}"></script>

    <script src="{{ asset('js/moment.min.js') }}"></script>

    <script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>

    <script src="{{ asset('js/bootstrap4-toggle.min.js') }}"></script>

    <script src="{{ asset('js/select2.min.js') }}"></script>

    {{-- <script src="{{ asset('js/select2.ajax.min.js') }}"></script> --}}

    <script src="{{ asset('js/bootstrapSwitch.min.js') }}"></script>
    <!-- JQuery UI 1.13.2 -->
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>

    <!-- Datatable JS 2.0.5-->

    {{--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/4.0.1/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdn.datatables.net/keytable/2.12.0/js/dataTables.keyTable.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap4.js"></script>
    --}}

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
    {{-- <script src="{{ asset('js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.fixedHeader.min.js') }}"></script> --}}
    <script src="{{ asset('js/dataTables.keyTable.min.js') }}"></script>

    <!-- new updated in cdn 9 june,25 -->
    <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/responsive.bootstrap4.js') }}"></script>

    {{-- Toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="{{ asset('js/sweetalert2@11.js') }}"></script>
    <script src="{{ asset('js/app-toast.js') }}?v={{ @filemtime(public_path('js/app-toast.js')) }}"></script>
    {{-- Page libraries that must be ready before the page's inline scripts run --}}
    @stack('head_scripts')
    <script>AppNav.libsReady();</script>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div id="wait">

    </div>
    <div class="wrapper">
        <!-- Main Header -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            {{-- Section / department of the signed-in user, with today's date to its right --}}
            @php
                $topbarBn = app()->getLocale() === 'bn';
                $topbarDept = Auth::user()?->department;
                $topbarDeptName = $topbarDept
                    ? (($topbarBn ? ($topbarDept->name_bn ?: $topbarDept->name) : $topbarDept->name) ?: $topbarDept->name)
                    : null;
                $topbarToday = now()->locale($topbarBn ? 'bn' : 'en')->translatedFormat($topbarBn ? 'l, j F Y' : 'l, d M Y');
                if ($topbarBn) {
                    $topbarToday = strtr($topbarToday, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
                }
            @endphp
            <div class="app-topbar-context">
                @if ($topbarDeptName)
                    <span class="app-topbar-dept" title="{{ $topbarDeptName }}">{{ $topbarDeptName }}</span>
                @endif
                <span class="app-topbar-date" title="{{ __('requisition.manage.today') }}">
                    <i class="fas fa-calendar-day" aria-hidden="true"></i>
                    <span class="sr-only">{{ __('requisition.manage.today') }}:</span>
                    <time datetime="{{ now()->format('Y-m-d') }}">{{ $topbarToday }}</time>
                </span>
            </div>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link app-bell {{ isset($unreadCount) && $unreadCount > 0 ? 'has-unread' : '' }}" data-toggle="dropdown" href="#"
                        role="button" aria-label="{{ app()->getLocale() === 'bn' ? 'নোটিফিকেশন' : 'Notifications' }}"
                        title="{{ app()->getLocale() === 'bn' ? 'নোটিফিকেশন' : 'Notifications' }}">
                        <i class="fa fa-bell" aria-hidden="true"></i>
                        @if (isset($unreadCount) && $unreadCount > 0)
                            <span class="badge navbar-badge app-bell-count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </a>
                    {{-- Notification panel: header with the unread count, the latest unread notifications
                         (an icon by kind, the text on as many lines as it needs, how long ago), a link to all. --}}
                    @php
                        $notifyBn = app()->getLocale() === 'bn';
                        $notifyDigits = fn ($value) => $notifyBn
                            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
                            : (string) $value;
                        $notifyCount = (int) ($unreadCount ?? 0);
                        // Icon and colour by what the notification is about
                        $notifyKind = function (string $text) {
                            $lower = strtolower($text);
                            return match (true) {
                                str_contains($lower, 'reject') => ['fa-times-circle', 'is-danger'],
                                str_contains($lower, 'deliver') => ['fa-truck', 'is-success'],
                                str_contains($lower, 'approved') => ['fa-check-circle', 'is-info'],
                                str_contains($lower, 'pending') => ['fa-hourglass-half', 'is-warning'],
                                default => ['fa-bell', 'is-neutral'],
                            };
                        };
                    @endphp
                    <div class="dropdown-menu dropdown-menu-right app-notify" aria-label="{{ $notifyBn ? 'নোটিফিকেশন' : 'Notifications' }}">
                        <div class="app-notify-head">
                            <span class="app-notify-title">{{ $notifyBn ? 'নোটিফিকেশন' : 'Notifications' }}</span>
                            @if ($notifyCount > 0)
                                <span class="app-notify-count">{{ $notifyBn ? $notifyDigits($notifyCount) . 'টি নতুন' : $notifyCount . ' new' }}</span>
                            @endif
                        </div>

                        <div class="app-notify-list">
                            @forelse (($recentNotifications ?? collect()) as $notification)
                                @php
                                    [$notifyIcon, $notifyTone] = $notifyKind((string) $notification->notification_text);
                                    $notifyAt = $notification->created_at->timezone('Asia/Dhaka');
                                @endphp
                                <a href="{{ route('notifications.read', $notification->id) }}" class="app-notify-item">
                                    <span class="app-notify-icon {{ $notifyTone }}" aria-hidden="true"><i class="fas {{ $notifyIcon }}"></i></span>
                                    <span class="app-notify-body">
                                        <span class="app-notify-text">{{ $notification->notification_text }}</span>
                                        <span class="app-notify-time" title="{{ $notifyAt->format('d M, Y h:i A') }}">
                                            <i class="fas fa-clock" aria-hidden="true"></i>
                                            {{ $notifyDigits($notifyAt->copy()->locale($notifyBn ? 'bn' : 'en')->diffForHumans()) }}
                                            <span class="app-notify-date">· {{ $notifyDigits($notifyAt->copy()->locale($notifyBn ? 'bn' : 'en')->translatedFormat($notifyBn ? 'j M, g:i A' : 'd M, h:i A')) }}</span>
                                        </span>
                                    </span>
                                    <span class="app-notify-dot" aria-hidden="true"></span>
                                </a>
                            @empty
                                <div class="app-notify-empty">
                                    <span class="app-notify-empty-icon" aria-hidden="true"><i class="fas fa-bell-slash"></i></span>
                                    <p>{{ $notifyBn ? 'নতুন কোনো নোটিফিকেশন নেই' : 'No new notifications' }}</p>
                                </div>
                            @endforelse
                        </div>

                        <a href="{{ route('notifications.index') }}" wire:navigate class="app-notify-foot">
                            {{ $notifyBn ? 'সব নোটিফিকেশন দেখুন' : 'See all notifications' }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    {{-- Language: a two-part switch (checked = English). The page script reacts to #languageToggle. --}}
                    <label class="app-lang" title="{{ app()->getLocale() === 'bn' ? 'ভাষা পরিবর্তন' : 'Change language' }}">
                        <input type="checkbox" id="languageToggle" {{ app()->getLocale() === 'en' ? 'checked' : '' }}
                            aria-label="{{ app()->getLocale() === 'bn' ? 'ভাষা পরিবর্তন' : 'Change language' }}">
                        <span class="app-lang-track" aria-hidden="true">
                            <span class="app-lang-thumb"></span>
                            <span class="app-lang-opt app-lang-bn">বাং</span>
                            <span class="app-lang-opt app-lang-en">EN</span>
                        </span>
                    </label>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ asset('theme/assets/img/gov.png') }}" class="user-image" alt="User Image">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <!-- User image -->
                        <li class="user-header bg-primary">
                            <img src="{{ asset('theme/assets/img/gov.png') }}" class="img-circle elevation-2"
                                alt="User Image">
                            <p>
                                {{ Auth::user()->name }}
                                <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <a href="{{ route('profile.index') }}" wire:navigate class="btn btn-default btn-flat">Profile</a>
                            <a href="#" class="btn btn-default btn-flat float-right"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Sign out
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Left side column. contains the logo and sidebar -->
        @include('layouts.sidebar')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
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
                {{ __('footer.copyright') }} &copy; <?php echo date('Y'); ?> {{ __('common.app_name') }} | {{ __('common.ministry') }}
            </strong> |
            {{ __('common.footer_copyright') }}
        </footer>
    </div>

    <script>
        $(function() {
            bsCustomFileInput.init();
        });
        // For Localication
        var selectedLocale = "{{ app()->getLocale() === 'en' ? 'en' : 'bn' }}";

        // For Email validation
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Select2
        // $('.js-example-basic-single').select2({
        //     placeholder: 'Select an option'
        // });

        $(document).ready(function() {
            // $('.js-example-basic-multiple').select2();
            $('.select2').select2();
            $('#languageToggle').change(function() {
                var locale = $(this).prop('checked') ? 'en' : 'bn';
                var url = "{{ route('change.language', ['locale' => ':locale']) }}";
                // Show loader
                $('#wait').show();
                window.location.href = url.replace(':locale', locale);
            });
        });

        // Set Toastr options
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
        };
    </script>

    <!-- CSRF Token setup script -->
    <script>
        // Get the CSRF token value from the meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Set the CSRF token in the headers of all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // Session expired while the page was open: any background request that is refused
        // for that reason (419 expired page, 401 signed out) opens the login page.
        if (! window.__sessionExpiryBound) {
            window.__sessionExpiryBound = true;
            window.__loginUrl = @json(route('login'));
            $(document).ajaxError(function (event, xhr) {
                if (xhr && (xhr.status === 419 || xhr.status === 401)) {
                    window.location.href = window.__loginUrl;
                }
            });
            document.addEventListener('livewire:init', function () {
                Livewire.hook('request', function (request) {
                    request.fail(function (failure) {
                        if (failure.status === 419 || failure.status === 401) {
                            failure.preventDefault();
                            window.location.href = window.__loginUrl;
                        }
                    });
                });
            });
        }
    </script>
    @stack('third_party_scripts')
    @stack('page_scripts')
    @stack('js')
</body>

</html>
