<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>River Project</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('theme/assets/img/gov.png') }}" rel="icon">
    <link href="{{ asset('theme/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->

    <link href="{{ asset('theme/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="{{ asset('theme/assets/css/main.css') }}" rel="stylesheet">

</head>


<body class="index-page" onload="initMap()">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">
            <a href="{{ route('welcome') }}" class="logo d-flex align-items-center me-auto">
                <img src="{{ asset('theme/assets/img/gov.png') }}" style="max-height: 56px;">
            </a>
            <nav id="navmenu" class="navmenu">
                <ul>
                    @if (Route::has('login'))
                        @auth
                            <li><a href="{{ url('/home') }}" class="active">Home</a></li>
                        @else
                            <li><a href="#" class="active">Notice</a></li>
                        @endauth
                    @endif
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            @if (!isset(Auth::user()->name))
                <a href="{{ route('login') }}" class="btn-getstarted">Log in</a>
            @endif
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>

    <footer id="footer" class="footer">

        <div class="container copyright text-center mt-4">
            <div class="credits">
                © 2024 - Dhaka Sanitation Improvement Project (DSIP)-v1.7
            </div>
        </div>

    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('theme/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('theme/assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('theme/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('theme/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('theme/assets/vendor/waypoints/noframework.waypoints.js') }}"></script>
    <script src="{{ asset('theme/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('theme/assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>

    <!-- Main JS File -->
    <script src="{{ asset('theme/assets/js/main.js') }}"></script>
    @stack('js')
</body>

</html>
