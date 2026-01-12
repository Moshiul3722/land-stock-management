<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Services\LotService;
use App\Services\ProductCategoryService;
use App\Services\ProductPurchaseTypeService;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LotController extends Controller
{
    protected $lotService;
    protected $productService;
    protected $productCategoryService;
    protected $productPurchaseTypeService;

    public function __construct(
        LotService $lotService,
        ProductService $productService,
        ProductCategoryService $productCategoryService,
        ProductPurchaseTypeService $productPurchaseTypeService,
    ) {
        $this->lotService = $lotService;
        $this->productService = $productService;
        $this->productCategoryService = $productCategoryService;
        $this->productPurchaseTypeService = $productPurchaseTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // Define the possible statuses for the filter dropdown
        $statuses = ['draft', 'pending', 'approved', 'rejected'];
        // Handle AJAX requests for filtering
        if ($request->ajax()) {
            $statusFilter = $request->input('status');
            $lots = $this->lotService->fetchAll($statusFilter);
            return view('lot._lot_list', compact('lots')); // Return the partial view
        }
        // For initial page load, pass statuses to the view
        return view('lot.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();
        return view('lot.create', compact('categories', 'purchaseTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Determine which button was clicked from its 'name' attribute
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
        // Eager load relationships for the detailed view
        $lot->load('lotDetails.product');
        return view('lot.show', compact('lot'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lot $lot)
    {
        // Prevent editing if not in draft status
        if ($lot->status !== 'draft') {
            return redirect()->route('lots.show', $lot)->with('error', 'This lot cannot be edited.');
        }
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();
        $lot->load('lotDetails.product.category');
        return view('lot.edit', compact('lot', 'categories', 'purchaseTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lot $lot)
    {
        try {
            // Read the 'action' from the request input
            $action = $request->input('action', 'submit');

            // Pass the action to the service
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
     * Display a listing of the resource.
     * View the lot requests
     */
    public function entryIndex(Request $request)
    {
        // Get all lots that have a 'pending' status
        $pendingLots = $this->lotService->getAllPendingLots();

        return view('lot.entry.index', compact('pendingLots'));
    }
    
    /**
     * Approve a pending lot.
     */
    public function approve(Request $request, Lot $lot)
    {
        try {
            $this->lotService->approveLot($lot, $request->input('comments'));
            return redirect()->route('lots.entry.index')->with('success', 'Lot approved successfully and items added to stock.');
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

    /**
     * Get products by sub-category for AJAX requests.
     */
    public function getBySubCategory(Request $request, $subCategoryId)
    {
        if ($request->ajax()) {
            $products = \App\Models\Product::where('sub_category_id', $subCategoryId)->get();
            return response()->json($products);
        }
        abort(403, 'This action is unauthorized.');
    }
}
