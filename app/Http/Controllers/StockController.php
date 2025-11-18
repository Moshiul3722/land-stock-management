<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use App\Services\ProductCategoryService;
use App\Services\ProductPurchaseTypeService;
use Illuminate\Http\Request;

class StockController extends Controller
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
     * Display the aggregated stock list.
     */
    public function index(Request $request)
    {
        // Fetch filters for dropdowns
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();

        // Handle AJAX requests for filtering
        if ($request->ajax()) {
            $filters = $request->only(['category_id', 'purchase_type_id']);
            $stockSummary = $this->stockService->getAggregatedStockList($filters);
            return view('stock._stock_list', compact('stockSummary'));
        }

        // For initial page load
        return view('stock.index', compact('categories', 'purchaseTypes'));
    }

    /**
     * Display the stock details for a specific product, broken down by lot.
     */
    public function show(Product $product)
    {
        // Eager load the related stock entries, and for each entry, load its lot and purchase type
        $product->load('stocks.lot.purchaseType');

        // Filter out any stock entries that might be 0
        $stockDetails = $product->stocks->where('quantity', '>', 0);

        return view('stock.show', compact('product', 'stockDetails'));
    }
}
