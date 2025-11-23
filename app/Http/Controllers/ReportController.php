<?php

namespace App\Http\Controllers;

use App\Services\ProductCategoryService;
use App\Services\ProductPurchaseTypeService;
use App\Services\StockService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $stockService;
    protected $productCategoryService;
    protected $productPurchaseTypeService;

    public function __construct(
        StockService $stockService,
        ProductCategoryService $productCategoryService,
        ProductPurchaseTypeService $productPurchaseTypeService
    ) {
        $this->stockService = $stockService;
        $this->productCategoryService = $productCategoryService;
        $this->productPurchaseTypeService = $productPurchaseTypeService;
    }

    /**
     * Display the Current Stock Report.
     */
    public function currentStock(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['category_id', 'product_id', 'purchase_type_id']);
            $stockSummary = $this->stockService->getAggregatedStockList($filters);

            // Return the partial view from the new location
            return view('reports.stock._list', compact('stockSummary'));
        }

        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();

        // Return the main view from the new location
        return view('reports.stock.index', compact('categories', 'purchaseTypes'));
    }
}
