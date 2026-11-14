@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>325</h3>
                        <p>Requisitions Processed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                </div>
            </div> --}}

            <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $userCount }}</h3>
                        <p>ব্যবহারকারী</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $roleCount }}</h3>
                        <p>রোল</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $categoryCount }}</h3>
                        <p>প্রোডাক্ট ক্যাটাগরী</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $productCount }}</h3>
                        <p>প্রোডাক্ট</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
            </div>

            {{-- <div class="col-lg-4 col-6">
                <!-- small box -->
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>543</h3>
                        <p>Products on Track</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div> --}}
        </div>
        <!-- Bar chart row -->
        <div class="row">
            <!-- Left col -->
            <div class="col-md-6">
                <!-- BAR CHART -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Requisition Submitted by Months</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="divisionBarChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <!-- /.col -->

            <div class="col-md-6">
                <!-- Pie/Donut Chart -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Requisitions By Department</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="pieChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <!-- /.col -->
        </div>
    </div>
@endsection
@push('head_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
@push('js')
    <script>
        $(function() {
            // --- 1. BAR CHART (Monthly) ---
            // Get data passed from the controller
            var barData = @json($barChartData);
            var ctx = document.getElementById('divisionBarChart').getContext('2d');
            var barChart1 = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: barData.labels,
                    datasets: [{
                        label: 'Requisitions',
                        data: barData.data, // Dynamic Data (Counts)
                        backgroundColor: 'rgba(60,141,188,0.9)',
                        borderColor: 'rgba(60,141,188,0.8)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 } // Ensure integers for counts
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // --- 2. PIE CHART (Departments) ---
            // Get data passed from the controller
            var pieData = @json($pieChartData);

            // Generate dynamic colors based on data length
            var colors = [
                '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de',
                '#8e44ad', '#34495e', '#16a085', '#e67e22'
            ];

            var ctxPie = document.getElementById('pieChart').getContext('2d');
            var pieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: pieData.labels, // Dynamic Labels (Departments)
                    datasets: [{
                        data: pieData.data, // Dynamic Data (Counts)
                        backgroundColor: colors.slice(0, pieData.data.length),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        });
    </script>
@endpush

