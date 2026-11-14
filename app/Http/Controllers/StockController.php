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

        // For initial page load: the whole list is on the page; the category filter, the search
        // box and the paging work in the browser.
        $stockSummary = $this->stockService->getAggregatedStockList([]);

        // Unit of each product as shown on screen (Bangla name on Bangla pages), by product id.
        $listedProducts = Product::query()
            ->whereIn('id', $stockSummary->pluck('product_id')->unique())
            ->get(['id', 'name', 'name_bn', 'product_unit']);
        $unitLabels = $listedProducts
            ->mapWithKeys(fn (Product $product) => [
                $product->id => $product->product_unit && ! is_numeric($product->product_unit) ? $product->unit_label : '',
            ]);
        // Bangla name of each product, shown in brackets beside the name (empty when it has none
        // or is the same as the name), by product id.
        $banglaNames = $listedProducts
            ->mapWithKeys(fn (Product $product) => [
                $product->id => trim((string) $product->name_bn) !== '' && $product->name_bn !== $product->name ? $product->name_bn : '',
            ]);

        return view('stock.index', compact('categories', 'purchaseTypes', 'stockSummary', 'unitLabels', 'banglaNames'));
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

    /**
     * Who received a product through requisitions: one row per issue, with the section /
     * department of the person it was issued to, the date and the quantity. Newest first.
     */
    public function issues(Product $product)
    {
        $issues = \App\Models\IssuedItem::query()
            ->where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->with(['user:id,name,department_id', 'user.department:id,name,name_bn'])
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->get(['id', 'user_id', 'requisition_id', 'product_id', 'quantity', 'issued_date']);

        $unitLabel = $product->product_unit && ! is_numeric($product->product_unit) ? $product->unit_label : '';

        return view('stock.issues', compact('product', 'issues', 'unitLabel'));
    }

    /**
     * Distributed goods: the requisitions through which goods were handed over to users, one
     * row per requisition (requester, date and time of the handover, the products). Every
     * section / department is listed. Newest first.
     */
    public function departmentIssues()
    {
        // Everything issued through a requisition, newest first (all sections / departments).
        $issues = \App\Models\IssuedItem::query()
            ->whereNull('deleted_at')
            ->whereNotNull('requisition_id')
            ->with(['product:id,name'])
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->get(['id', 'requisition_id', 'product_id', 'issued_date', 'created_at']);

        $requisitions = \App\Models\Requisition::query()
            ->whereIn('id', $issues->pluck('requisition_id')->unique())
            ->with(['requestedBy:id,name,department_id', 'requestedBy.department:id,name,name_bn'])
            ->get()
            ->keyBy('id');

        // One row per requisition: who asked, when the goods were handed over and which products.
        $distributions = $issues
            ->groupBy('requisition_id')
            ->map(function ($items, $requisitionId) use ($requisitions) {
                $latest = $items->sortByDesc(fn ($item) => $item->created_at ?? $item->issued_date)->first();

                return (object) [
                    'requisition' => $requisitions->get($requisitionId),
                    'issued_date' => $latest->issued_date,
                    'issued_at' => $latest->created_at,
                    'products' => $items->pluck('product.name')->filter()->unique()->values(),
                ];
            })
            ->filter(fn ($row) => $row->requisition !== null)
            ->sortByDesc(fn ($row) => (string) ($row->issued_at ?? $row->issued_date))
            ->values();

        return view('stock.department_issues', compact('distributions'));
    }

    /**
     * Get the available stock for a single product.
     */
    public function checkProductStatus(Product $product)
    {
        $stockSummary = $this->stockService->getAggregatedStockList(['product_id' => $product->id]);

        $totalQuantity = $stockSummary->first()->total_quantity ?? 0;

        if ($totalQuantity > 0) {
            return response()->json([
                'status' => 'Available',
                'status_bn' => 'মজুদ আছে',
                'class' => 'badge bg-success',
                'quantity' => $totalQuantity
            ]);
        }

        return response()->json([
            'status' => 'Not Available',
            'status_bn' => 'মজুদ নেই',
            'class' => 'badge bg-danger',
            'quantity' => 0
        ]);
    }
}
