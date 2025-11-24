<?php

namespace App\Services;

use App\Services\ProductCategoryService;
use App\Services\ProductService;
use App\Services\RequisitionService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Repositories\RequisitionRepository;

class DashboardService
{
    protected $userService;
    protected $roleService;
    protected $productService;
    protected $productCategoryService;
    protected $requisitionService;
    protected $requisitionRepository;

    public function __construct(
        UserService $userService,
        RoleService $roleService,
        ProductService $productService,
        ProductCategoryService $productCategoryService,
        RequisitionService $requisitionService,
        RequisitionRepository $requisitionRepository
    ) {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->productService = $productService;
        $this->productCategoryService = $productCategoryService;
        $this->requisitionService = $requisitionService;
        $this->requisitionRepository = $requisitionRepository;
    }

    /**
     * Gathers all data required for the main dashboard.
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        // 1. Prepare Bar Chart Data (Monthly)
        $monthlyData = $this->requisitionRepository->getMonthlyCounts();
        $barChart = [
            'labels' => $monthlyData->pluck('month_name')->toArray(),
            'data' => $monthlyData->pluck('count')->toArray(),
        ];

        // 2. Prepare Pie Chart Data (Departments)
        $deptData = $this->requisitionRepository->getCountsByDepartment();
        $pieChart = [
            'labels' => $deptData->pluck('department_name')->toArray(),
            'data' => $deptData->pluck('count')->toArray(),
        ];

        return [
            'userCount' => $this->userService->getTotalCount(),
            'roleCount' => $this->roleService->getTotalCount(),
            'productCount' => $this->productService->getTotalCount(),
            'categoryCount' => $this->productCategoryService->getTotalCount(),
            'processedRequisitionsCount' => $this->requisitionService->getProcessedCount(),
            
            // Chart Data
            'barChartData' => $barChart,
            'pieChartData' => $pieChart,
        ];
    }
}