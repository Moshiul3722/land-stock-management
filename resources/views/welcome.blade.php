@extends('layouts.public')

@section('content')
    {{-- Hero Section --}}
    <div class="hero-section text-center">
        <div class="container">
            {{-- Content remains the same --}}
            <h1 class="display-4 fw-bold">Ministry of Land</h1>
            <h2 class="display-6">Inventory Management System</h2>
            <p class="lead col-md-8 mx-auto mt-3">
                Efficiently manage products, track stock, and streamline approvals for Ministry supplies.
            </p>
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Authorized Login
                </a>
            @else
                 <a href="{{ route('home') }}" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                </a>
            @endguest
        </div>
    </div>

    {{-- Features Section --}}
    <div class="features-section"> {{-- Added class --}}
        <div class="container">
            <div class="row text-center g-4">
                 {{-- Content remains the same --}}
                <div class="col-md-4">
                    <div class="feature-icon mb-3"><i class="fas fa-boxes-stacked"></i></div>
                    <h4 class="fw-semibold mb-0">Organized Inventory</h4>
                    <p class="text-muted">Easily manage product categories and details.</p>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon mb-3"><i class="fas fa-clipboard-check"></i></div>
                    <h4 class="fw-semibold mb-0">Streamlined Approvals</h4>
                    <p class="text-muted">Clear, defined workflows for stock entry (Lots).</p>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon mb-3"><i class="fas fa-chart-line"></i></div>
                    <h4 class="fw-semibold mb-0">Real-time Tracking</h4>
                    <p class="text-muted">Know your stock levels and approval statuses instantly.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Benefits Section --}}
    <div class="benefits-section"> {{-- Added class --}}
        <div class="container">
            <div class="row align-items-center">
                 {{-- Content remains the same --}}
                 <div class="col-md-7">
                    <h3 class="fw-bold">Efficiency for the Ministry</h3>
                    <p class="text-muted">
                        This system, developed by UltraCore Creatives Ltd., aims to reduce manual work, minimize errors,
                        and provide clear accountability in managing the Ministry's valuable resources. Access requires authorized credentials.
                    </p>
                </div>
                <div class="col-md-5 text-center">
                     <img src="{{ asset('img/inventory-management.jpg') }}" alt="Inventory Management Illustration" class="img-fluid" style="max-height: 250px;">
                </div>
            </div>
        </div>
    </div>
@endsection