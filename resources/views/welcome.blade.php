@extends('layouts.public')

@section('content')
    <div class="container main-container">
        <div class="row">
            <!-- Left Column: Hero/Slider Area -->
            <div class="col-lg-9 col-md-8">
                <!-- Fake Slider/Hero Image -->
                <div class="card border-0 mb-4 shadow-sm">
                    <!-- Replace this URL with a relevant image of the ministry or inventory warehouse -->
                    <img src="{{ asset('img/pub-hero.jpg') }}" class="card-img-top" alt="Hero Image"
                        style="height: 350px; object-fit: cover;">
                    <div class="card-body bg-light border-top">
                        <marquee direction="left" class="text-danger fw-bold">
                            ** Important Notice: Please collect your credentials from Admin Section **
                        </marquee>
                    </div>
                </div>

                <!-- Services Section -->
                <div class="card mb-4 border-success">
                    <div class="card-header bg-white border-bottom border-success">
                        <h5 class="mb-0 text-success fw-bold"><i class="fas fa-th-large me-2"></i> Inventory Management System - Ministry of Land</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 text-center">
                            <!-- Inventory System Access Card -->
                            <div class="col-md-4 col-sm-6">
                                <div class="p-3 border rounded bg-light h-100 hover-effect position-relative">
                                    @guest
                                        <a href="{{ route('login') }}" class="stretched-link"></a>
                                    @else
                                        <a href="{{ route('home') }}" class="stretched-link"></a>
                                    @endguest

                                    <div class="icon-box mb-3">
                                        <i class="fas fa-boxes fa-3x text-success"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">Inventory System</h5>
                                    <p class="text-muted small mb-0">Manage stock, requisitions, and assets.</p>
                                </div>
                            </div>

                            <!-- Placeholder 1 -->
                            <div class="col-md-4 col-sm-6">
                                <div class="p-3 border rounded bg-white h-100 hover-effect position-relative">
                                    @guest
                                        <a href="{{ route('login') }}" class="stretched-link"></a>
                                    @else
                                        <a href="{{ route('home') }}" class="stretched-link"></a>
                                    @endguest
                                    <div class="icon-box mb-3"><i class="fas fa-file-invoice fa-3x text-success"></i></div>
                                    <h5 class="fw-bold">Requisitions</h5>
                                    <p class="small mb-0">Internal requests module.</p>
                                </div>
                            </div>

                            <!-- Placeholder 2 -->
                            <div class="col-md-4 col-sm-6">
                                <div class="p-3 border rounded bg-white h-100 hover-effect position-relative">
                                    @guest
                                        <a href="{{ route('login') }}" class="stretched-link"></a>
                                    @else
                                        <a href="{{ route('home') }}" class="stretched-link"></a>
                                    @endguest
                                    <div class="icon-box mb-3"><i class="fas fa-chart-pie fa-3x text-success"></i></div>
                                    <h5 class="fw-bold">Reports</h5>
                                    <p class="small mb-0">Annual stock reports.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Section -->
                <div class="card border-0">
                    <div class="card-body">
                        <h4 class="text-success fw-bold border-bottom pb-2 mb-3">About This System Aha</h4>
                        <p class="text-justify">
                            The <strong>Ministry of Land Inventory Management System</strong> is a secure, internal platform
                            designed to streamline the procurement, storage, and distribution of office supplies and assets.
                            This digital initiative ensures transparency, reduces wastage, and improves the efficiency of
                            the Ministry's daily operations.
                        </p>
                        <p>
                            Developed by <strong>UltraCore Creatives Ltd.</strong>, this system features real-time stock
                            tracking, digital requisition workflows, and comprehensive audit trails.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar/Widgets -->
            <div class="col-lg-3 col-md-4">
                <!-- Login Widget (If Guest) -->
                @guest
                    <div class="card border-success mb-4">
                        <div class="card-header bg-success text-white text-center fw-bold">
                            <i class="fas fa-lock me-1"></i> Authorized Login
                        </div>
                        <div class="card-body text-center">
                            <p class="small text-muted">Access is restricted to Ministry of Land officials.</p>
                            <a href="{{ route('login') }}" class="btn btn-danger w-100 mb-2">Login to System</a>
                        </div>
                    </div>
                @else
                    <!-- User Profile Widget (If Logged In) -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white text-center fw-bold">
                            <i class="fas fa-user-circle me-1"></i> Welcome
                        </div>
                        <div class="card-body text-center">
                            <h6 class="fw-bold">{{ Auth::user()->name }}</h6>
                            <p class="small text-muted mb-3">{{ Auth::user()->email }}</p>
                            <a href="{{ route('home') }}" class="btn btn-outline-primary w-100">Go to Dashboard</a>
                        </div>
                    </div>
                @endguest

                <!-- Important Links Widget -->
                <div class="card mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="fas fa-link me-1 text-success"></i> Quick Links
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item"><a href="#" class="text-decoration-none text-dark"><i
                                    class="fas fa-caret-right text-success me-2"></i> User Manual</a></li>
                        {{-- <li class="list-group-item"><a href="#" class="text-decoration-none text-dark"><i
                                    class="fas fa-caret-right text-success me-2"></i> Requisition Policy</a></li>
                        <li class="list-group-item"><a href="#" class="text-decoration-none text-dark"><i
                                    class="fas fa-caret-right text-success me-2"></i> Support Ticket</a></li> --}}
                    </ul>
                </div>

                <!-- Emergency Contact -->
                <div class="card bg-light border-0">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-danger">System Support</h6>
                        <p class="small mb-1">For technical issues:</p>
                        <a href="tel:+880123456789" class="btn btn-sm btn-outline-dark"><i class="fas fa-phone-alt"></i>
                            01234-567890</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-effect {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #28a745 !important;
        }
    </style>
@endsection
