<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\FiscalYear;
use App\Models\Product;
use App\Services\ApprovalService;
use App\Services\LotService;
use App\Services\ProductCategoryService;
use App\Services\ProductPurchaseTypeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LotController extends Controller
{
    protected $lotService;
    protected $productCategoryService;
    protected $productPurchaseTypeService;
    protected $approvalService;

    public function __construct(
        LotService $lotService,
        ProductCategoryService $productCategoryService,
        ProductPurchaseTypeService $productPurchaseTypeService,
        ApprovalService $approvalService
    ) {
        $this->lotService = $lotService;
        $this->productCategoryService = $productCategoryService;
        $this->productPurchaseTypeService = $productPurchaseTypeService;
        $this->approvalService = $approvalService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statuses = ['draft', 'pending', 'approved', 'rejected'];
        if ($request->ajax()) {
            $statusFilter = $request->input('status');
            $lots = $this->lotService->fetchAll($statusFilter);
            return view('lot._lot_list', compact('lots'));
        }
        return view('lot.index', compact('statuses'));
    }

    /**
     * Display a listing of lot requests pending approval.
     */
    public function entryIndex(Request $request)
    {
        $pendingLots = $this->lotService->getAllPendingLots();
        return view('lot.entry.index', compact('pendingLots'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();

        // Fetch Active Fiscal Years sorted descending
        $fiscalYears = FiscalYear::where('is_active', true)->orderBy('name', 'desc')->get();

        // Determine Current Fiscal Year ID based on today's date
        $today = date('Y-m-d');
        $currentFiscalYear = $fiscalYears->first(function ($year) use ($today) {
            return $today >= $year->start_date && $today <= $year->end_date;
        });
        $currentFiscalYearId = $currentFiscalYear ? $currentFiscalYear->id : null;

        return view('lot.create', compact('categories', 'purchaseTypes', 'fiscalYears', 'currentFiscalYearId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $action = $request->input('action', 'submit');
            $this->lotService->store($request->all(), $action);
            $message = ($action === 'save_draft')
                ? 'Lot saved as draft successfully.'
                : 'Lot created and submitted for approval successfully.';
            return redirect()->route('lots.index')->with('success', $message);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lot $lot)
    {
        $lot->load('lotDetails.product.category', 'lotDetails.product.subCategory', 'purchaseType', 'fiscalYear');
        return view('lot.show', compact('lot'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lot $lot)
    {
        if ($lot->status !== 'draft') {
            return redirect()->route('lots.show', $lot)->with('error', 'This lot cannot be edited as it is already in process.');
        }
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();
        $fiscalYears = FiscalYear::where('is_active', true)->orderBy('name', 'desc')->get();

        $lot->load('lotDetails.product.category');
        return view('lot.edit', compact('lot', 'categories', 'purchaseTypes', 'fiscalYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lot $lot)
    {
        try {
            $action = $request->input('action', 'submit');
            $this->lotService->update($request->all(), $lot->id, $action);
            $message = ($action === 'save_draft')
                ? 'Lot draft updated successfully.'
                : 'Lot submitted for approval successfully.';
            return redirect()->route('lots.index')->with('success', $message);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lot $lot)
    {
        try {
            $this->lotService->destroy($lot->id);
            return response()->json(['success' => true, 'message' => 'Lot deleted successfully.']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve a pending lot.
     */
    public function approve(Request $request, Lot $lot)
    {
        try {
            $this->lotService->approveLot($lot, $request->input('comments'));
            return redirect()->route('lots.entry.index')->with('success', 'Lot approved successfully, items stocked, and budget updated.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a pending lot.
     */
    public function reject(Request $request, Lot $lot)
    {
        try {
            $this->lotService->rejectLot($lot, $request->input('comments'));
            return redirect()->route('lots.entry.index')->with('success', 'Lot rejected successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get products by category for AJAX requests.
     */
    public function getByCategory(Request $request, $categoryId)
    {
        if ($request->ajax()) {
            $products = Product::where('category_id', $categoryId)->get();
            return response()->json($products);
        }
        abort(403, 'This action is unauthorized.');
    }

    /**
     * Get products by sub-category for AJAX requests.
     */
    public function getBySubCategory(Request $request, $subCategoryId)
    {
        if ($request->ajax()) {
            $products = Product::where('sub_category_id', $subCategoryId)->get();
            return response()->json($products);
        }
        abort(403, 'This action is unauthorized.');
    }

    /**
     * Search for existing brands or specifications for autocomplete.
     */
    public function autocomplete(Request $request)
    {
        $results = $this->lotService->getAutocompleteSuggestions(
            $request->input('term'),
            $request->input('type')
        );

        return response()->json($results);
    }
}
