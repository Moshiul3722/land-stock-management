<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />

    <!-- Fonts: SolaimanLipi / Nunito -->
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Nunito', 'SolaimanLipi', sans-serif;
            background-color: #f4f7f6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Strip */
        .gov-header {
            background: #fff;
            border-bottom: 4px solid #006a4e; /* BD Green */
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .gov-logo { height: 50px; width: auto; }
        .ministry-name {
            color: #006a4e;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 0;
            line-height: 1.2;
        }
        .gov-subtitle {
            color: #d32f2f;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background-image: url('https://land.gov.bd/themes/responsive_npf/img/bg_pattern.png'); /* Subtle pattern */
        }

        /* Login Card */
        .login-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            flex-wrap: wrap;
        }

        /* Left Side (Banner/Info) */
        .login-banner {
            background: linear-gradient(135deg, #006a4e 0%, #004d38 100%);
            color: #fff;
            width: 50%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        /* Background image overlay for left side */
        .login-banner::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('{{ asset("img/dhaka_line_6.webp") }}') center/cover;
            opacity: 0.2;
            z-index: 0;
        }

        .banner-content { position: relative; z-index: 1; }

        /* Right Side (Form) */
        .login-form-container {
            width: 50%;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        .login-subtitle {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        /* Form Controls */
        .form-control {
            padding: 12px 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            background-color: #fcfcfc;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(0, 106, 78, 0.1);
            border-color: #006a4e;
        }
        .input-group-text {
            background-color: #fff;
            border-right: none;
            color: #006a4e;
        }
        .form-control { border-left: none; }

        /* Button */
        .btn-gov {
            background-color: #006a4e;
            color: #fff;
            padding: 12px;
            font-weight: 600;
            border-radius: 5px;
            width: 100%;
            border: none;
            transition: all 0.3s;
        }
        .btn-gov:hover {
            background-color: #00523c;
            color: #fff;
            transform: translateY(-1px);
        }

        /* Footer */
        .gov-footer {
            background-color: #e9ecef;
            color: #6c757d;
            text-align: center;
            padding: 15px 0;
            font-size: 0.85rem;
            border-top: 1px solid #dee2e6;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-banner { display: none; } /* Hide banner on mobile */
            .login-form-container { width: 100%; padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="gov-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                <img src="{{ asset('Government_Seal_of_Bangladesh.svg') }}"
                     alt="BD Gov Logo" class="gov-logo me-3">
                <div>
                    <h1 class="ministry-name">Ministry of Land</h1>
                    <div class="gov-subtitle">Government of the People's Republic of Bangladesh</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="login-card">
            <!-- Left Side: Banner -->
            <div class="login-banner">
                <div class="banner-content">
                    <img src="{{ asset('img/bd-logo-trans.png') }}" alt="Logo" style="height: 100px; margin-bottom: 20px;">
                    <h3 class="fw-bold mb-3">{{ config('app.name') }}</h3>
                    <p class="mb-4 opacity-75">Efficient management of ministry resources, assets, and requisitions through a centralized digital platform.</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-shield-alt fa-2x mb-2"></i>
                            <div class="small">Secure</div>
                        </div>
                        <div class="text-center mx-3">
                            <i class="fas fa-bolt fa-2x mb-2"></i>
                            <div class="small">Fast</div>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <div class="small">Reliable</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="login-form-container">
                <div class="text-center mb-4 d-md-none">
                    {{-- Mobile Logo --}}
                    <img src="{{ asset('img/bd-logo-trans.png') }}" height="60" alt="Logo">
                </div>

                <h2 class="login-title">Sign In</h2>
                <p class="login-subtitle">Enter your credentials to access the dashboard.</p>

                <form method="post" action="{{ url('/login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Email / Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="admin@land.gov.bd" required autofocus>
                        </div>
                        @error('email')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-secondary mb-0">Password</label>
                            {{-- Uncomment if password reset is implemented --}}
                            {{-- <a href="{{ route('password.request') }}" class="small text-decoration-none" style="color: #006a4e;">Forgot Password?</a> --}}
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Enter your password" required>
                        </div>
                        @error('password')
                            <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>


                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-gov shadow-sm">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>

                    <!-- User Manual Link (Optional) -->
                    <div class="text-center mt-4">
                        <a href="#" class="small text-decoration-none text-secondary">
                            <i class="fas fa-question-circle me-1"></i> Need Help? View User Manual
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="gov-footer">
        <div class="container">
            <span>
                © {{ date('Y') }} Ministry of Land. All Rights Reserveds. |
                Developed by <a href="#" class="text-decoration-none fw-bold" style="color: #006a4e;">UltraCore Creatives Ltd.</a>
            </span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
