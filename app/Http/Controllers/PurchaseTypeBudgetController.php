<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Services\PurchaseTypeBudgetService;
use App\Services\ProductPurchaseTypeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseTypeBudgetController extends Controller
{
    protected $purchaseTypeBudgetService;
    protected $productPurchaseTypeService;

    public function __construct(PurchaseTypeBudgetService $purchaseTypeBudgetService, ProductPurchaseTypeService $productPurchaseTypeService)
    {
        $this->purchaseTypeBudgetService = $purchaseTypeBudgetService;
        $this->productPurchaseTypeService = $productPurchaseTypeService;
    }

    public function index()
    {
        // The list, with create / edit / delete in modals, is one Livewire screen.
        return view('crud.host', ['component' => 'budget.purchase-type-budget-manager']);
    }

    public function create()
    {
        // Sidebar "create" menu: opens the create modal on the management screen.
        return redirect()->route('ptbudgets.index', ['create' => 1]);
    }

    public function store(Request $request)
    {
        try {
            $this->purchaseTypeBudgetService->store($request->all());
            return redirect()->route('ptbudgets.index')->with('success', 'Budget defined successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Old edit URL: opens the edit modal on the management screen.
        return redirect()->route('ptbudgets.index', ['edit' => (int) $id]);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->purchaseTypeBudgetService->update($request->all(), $id);
            return redirect()->route('ptbudgets.index')->with('success', 'Budget updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->purchaseTypeBudgetService->destroy($id);
            return response()->json(['success' => true, 'message' => 'Budget deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
