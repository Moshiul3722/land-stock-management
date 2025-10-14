<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @var DashboardService
     */
    protected $dashboardService;

    /**
     * Create a new controller instance.
     *
     * @param DashboardService $dashboardService
     * @return void
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dashboardData = $this->dashboardService->getDashboardData();
        
        return view('home', $dashboardData);
    }
}

