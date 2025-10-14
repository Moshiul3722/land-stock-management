<?php

namespace App\Services;

use App\Services\ProductCategoryService;
use App\Services\ProductService;
use App\Services\RoleService;
use App\Services\UserService;

class DashboardService
{
    protected $userService;
    protected $roleService;
    protected $productService;
    protected $productCategoryService;

    public function __construct(
        UserService $userService,
        RoleService $roleService,
        ProductService $productService,
        ProductCategoryService $productCategoryService
    ) {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->productService = $productService;
        $this->productCategoryService = $productCategoryService;
    }

    /**
     * Gathers all data required for the main dashboard.
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        return [
            'userCount' => $this->userService->getTotalCount(),
            'roleCount' => $this->roleService->getTotalCount(),
            'productCount' => $this->productService->getTotalCount(),
            'categoryCount' => $this->productCategoryService->getTotalCount(),
        ];
    }
}