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
        // Older jQuery list (kept for any remaining caller)
        if ($request->ajax() && ! $request->hasHeader('X-Livewire')) {
            $statusFilter = $request->input('status');
            $lots = $this->lotService->fetchAll($statusFilter);
            return view('lot._lot_list', compact('lots'));
        }

        // Lot management screen: list + modal CRUD (Livewire: App\Livewire\Stock\LotManager)
        return view('crud.host', ['component' => 'stock.lot-manager']);
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
        // Sidebar "create" menu: opens the create modal on the management screen.
        return redirect()->route('lots.index', ['create' => 1]);
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
        // Old edit URL: opens the edit modal on the management screen.
        return redirect()->route('lots.index', ['edit' => $lot->id]);
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
            return redirect()->route('lots.entry.index')->with('success', __('lot.requests.approved'));
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
            return redirect()->route('lots.entry.index')->with('success', __('lot.requests.rejected'));
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
