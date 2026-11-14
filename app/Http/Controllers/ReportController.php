<?php

namespace App\Http\Controllers;

use App\Repositories\IssuedItemRepository;
use App\Services\ProductCategoryService;
use App\Services\ProductPurchaseTypeService;
use App\Services\StockService;
use App\Models\Department;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $stockService;
    protected $productCategoryService;
    protected $productPurchaseTypeService;
    protected $issuedItemRepository;

    public function __construct(
        StockService $stockService,
        ProductCategoryService $productCategoryService,
        ProductPurchaseTypeService $productPurchaseTypeService,
        IssuedItemRepository $issuedItemRepository
    ) {
        $this->stockService = $stockService;
        $this->productCategoryService = $productCategoryService;
        $this->productPurchaseTypeService = $productPurchaseTypeService;
        $this->issuedItemRepository = $issuedItemRepository;
    }

    /**
     * Display the Current Stock Report.
     */
    public function currentStock(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['category_id', 'product_id', 'purchase_type_id']);
            // Period of the report: stock entries made between these two dates
            $filters['stock_date_from'] = $request->input('date_from');
            $filters['stock_date_to'] = $request->input('date_to');

            // One row per product: the brand / specification column is gone, so the entries of
            // the same product are added together.
            $stockSummary = $this->stockService->getAggregatedStockList($filters)
                ->groupBy('product_id')
                ->map(function ($rows) {
                    $row = clone $rows->first();
                    $row->total_quantity = $rows->sum('total_quantity');

                    return $row;
                })
                ->values();

            // Unit of each product as shown on screen (Bangla name on Bangla pages), by product id.
            $unitLabels = \App\Models\Product::query()
                ->whereIn('id', $stockSummary->pluck('product_id'))
                ->get(['id', 'product_unit'])
                ->mapWithKeys(fn ($product) => [
                    $product->id => $product->product_unit && ! is_numeric($product->product_unit) ? $product->unit_label : '',
                ]);

            // Return the partial view from the new location
            return view('reports.stock._list', compact('stockSummary', 'unitLabels'));
        }

        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();

        // Return the main view from the new location
        return view('reports.stock.index', compact('categories', 'purchaseTypes'));
    }


    /**
     * Display the Consumption (Products Out) Report.
     * Shows what has been issued to users/departments over a period.
     */
    public function consumption(Request $request)
    {
        // Handle AJAX request for table data
        if ($request->ajax()) {
            $filters = $request->only([
                'category_id',
                'product_id',
                'purchase_type_id',
                'department_id',
                'start_date',
                'end_date'
            ]);

            // Fetch consumption data from IssuedItemRepository
            $reportData = $this->issuedItemRepository->getConsumptionReport($filters);
            return view('reports.consumption._list', compact('reportData'));
        }

        // Handle Initial Page Load (Dropdown Data)
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();
        $departments = Department::all(); // Fetch all departments for the filter

        return view('reports.consumption.index', compact('categories', 'purchaseTypes', 'departments'));
    }
}
