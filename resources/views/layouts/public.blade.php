<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Inventory Management') }} - Ministry of Land</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa; /* Light grey background for the whole page */
        }
        .navbar {
            /* Keep navbar clean */
        }
        .hero-section {
            /* Option 1: Subtle Gradient Background */
            /* background: linear-gradient(135deg, #e3f2fd, #f8f9fa); Light blue to light grey */

            /* Option 2: Solid Light Color Background */
            /* background-color: #e9ecef; A slightly darker grey than body */

            /* Option 3: Background Image (Uncomment and replace path) */
            
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('{{ asset("img/pub-hero.jpg") }}');
            background-size: cover;
            background-position: center;
            color: #fff;
           

            padding: 5rem 0; /* Increased padding */
            margin-bottom: 3rem;
        }
        .hero-section h1, .hero-section h2 {
            /* text-shadow: 1px 1px 3px rgba(0,0,0,0.3); // Add text shadow if using background image */
        }
        .features-section {
             padding: 3rem 0;
             background-color: #ffffff; /* White background to contrast */
             border-top: 1px solid #dee2e6;
             border-bottom: 1px solid #dee2e6;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd; /* Bootstrap primary blue */
        }
         .benefits-section {
             padding: 3rem 0;
             /* Keep on default light grey body background */
        }
        .footer {
            background-color: #dee2e6; /* Darker grey footer */
            padding: 1.5rem 0;
            margin-top: 3rem;
            font-size: 0.9em;
            color: #6c757d; /* Muted text color */
        }
        .footer a {
             color: #495057;
             text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            {{-- Navbar content remains the same --}}
             <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="/img/bd_logo.png" alt="Ministry of Land" height="30" class="me-2">
                    {{ config('app.name', 'Inventory Management') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto"></ul>
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="btn btn-outline-primary" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a href="{{ route('home') }}" class="nav-link">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                 <a class="nav-link" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"> @csrf </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>

        <footer class="footer mt-auto py-3 text-center">
             <div class="container">
                <span >© {{ date('Y') }} Ministry of Land, Government of Bangladesh. Developed by <a href="https://ultracorecreatives.com" target="_blank">UltraCore Creatives Ltd.</a></span>
                 {{-- <img src="/path/to/ultracore-logo.png" alt="UltraCore Creatives Ltd." height="20" class="ms-2"> --}}
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>