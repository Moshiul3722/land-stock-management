<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Inventory Management') }} - Ministry of Land</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />

    <!-- Fonts: SolaimanLipi is standard for BD Gov, falling back to Nunito/Arial -->
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Nunito', 'SolaimanLipi', sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        /* Top Bar */
        .top-bar {
            background-color: #fcfcfc;
            border-bottom: 1px solid #eee;
            padding: 5px 0;
            font-size: 0.85rem;
            color: #666;
        }

        /* Official Header */
        .gov-header {
            background-color: #fff;
            padding: 15px 0;
        }

        .gov-logo {
            height: 70px;
            width: auto;
        }

        .ministry-title {
            color: #006a4e; /* BD Gov Green */
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            line-height: 1.2;
        }

        .ministry-subtitle {
            color: #d32f2f; /* BD Flag Red */
            font-size: 1rem;
            font-weight: 600;
        }

        /* Navigation Bar - The Green Strip */
        .gov-navbar {
            background: linear-gradient(to bottom, #009e58 0%, #008349 100%);
            border-bottom: 3px solid #f4d03f; /* Accent Yellow line often seen */
            padding: 0;
        }

        .gov-navbar .nav-link {
            color: #fff !important;
            font-weight: 500;
            padding: 10px 15px !important;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gov-navbar .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .gov-navbar .active {
            background-color: #006a4e;
        }

        /* Content Container */
        .main-container {
            background: #fff;
            min-height: 600px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #e0e0e0;
        }

        /* Footer */
        .gov-footer {
            background: url('https://land.gov.bd/themes/responsive_npf/img/footer_top_bg.png') repeat-x top left, #2b2b2b;
            color: #ddd;
            padding: 40px 0 20px;
            margin-top: auto;
            font-size: 0.9rem;
        }
        .footer-bottom {
            background-color: #1f1f1f;
            padding: 15px 0;
            margin-top: 20px;
        }
        .footer-link {
            color: #ccc;
            text-decoration: none;
            display: block;
            margin-bottom: 5px;
        }
        .footer-link:hover { color: #fff; }
    </style>
    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    <div id="app">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="container d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-calendar-alt me-1 text-success"></i> {{ date('l, d F, Y') }}
                </span>
            </div>
        </div>

        <!-- Main Header -->
        <div class="gov-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-1 col-2 text-center">
                        <!-- Use asset() for your local logo -->
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/84/Government_Seal_of_Bangladesh.svg/1200px-Government_Seal_of_Bangladesh.svg.png" 
                             alt="BD Gov Logo" class="gov-logo">
                    </div>
                    <div class="col-md-6 col-10">
                        <h1 class="ministry-title">Ministry of Land</h1>
                        <span class="ministry-subtitle">Government of the People's Republic of Bangladesh</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navbar -->
        {{-- <nav class="navbar navbar-expand-lg gov-navbar">
            <div class="container">
                <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#govNav" aria-controls="govNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon filter-white"></span>
                </button>
                <div class="collapse navbar-collapse" id="govNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ url('/') }}"><i class="fas fa-home"></i></a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link bg-danger text-white px-3 rounded-0" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt me-1"></i> Login
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link bg-warning text-dark px-3 rounded-0" href="{{ route('home') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav> --}}

        <main class="flex-grow-1">
            @yield('content')
        </main>

        <footer class="gov-footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-sm-4">
                                <h6 class="text-uppercase fw-bold text-success mb-3">Important Links</h6>
                                <a href="#" class="footer-link">President's Office</a>
                                <a href="#" class="footer-link">Prime Minister's Office</a>
                                <a href="#" class="footer-link">Cabinet Division</a>
                                <a href="#" class="footer-link">Ministry of Public Administration</a>
                            </div>
                            <div class="col-sm-4">
                                <h6 class="text-uppercase fw-bold text-success mb-3">Internal Services</h6>
                                <a href="#" class="footer-link">Land Development Tax</a>
                                <a href="#" class="footer-link">E-Mutation</a>
                                <a href="#" class="footer-link">R.S. Khatian</a>
                                <a href="#" class="footer-link">Inventory Management</a>
                            </div>
                            <div class="col-sm-4">
                                <h6 class="text-uppercase fw-bold text-success mb-3">Contact</h6>
                                <p class="small text-muted">
                                    Ministry of Land<br>
                                    Bangladesh Secretariat, Dhaka-1000<br>
                                    Email: info@minland.gov.bd
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center text-md-end">
                        <img src="{{ asset('img/bd-logo-trans.png') }}" height="120" width="100" alt="Gov Logos" class="img-fluid mb-3 p-2 rounded">
                    </div>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <div class="container">
                    <span class="small">
                        © {{ date('Y') }} Ministry of Land, All Rights Reserved. | 
                        Technical Assistance: <strong>UltraCore Creatives Ltd.</strong>
                    </span>
                </div>
            </div>
            {{-- <div class="footer-bottom text-center">
                <div class="container">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <span class="small">
                            © {{ date('Y') }} Ministry of Land, All Rights Reserved. | 
                            Technical Assistance:
                        </span>
                        <img src="{{ asset('theme/assets/img/uc-logo.png') }}" 
                             alt="Ultracore Creatives" 
                             style="height: 30px; width: auto; opacity: 0.8;">
                    </div>
                </div>
            </div> --}}
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>