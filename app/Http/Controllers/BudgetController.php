<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Services\BudgetService;
use App\Services\ProductCategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BudgetController extends Controller
{
    protected $budgetService;
    protected $categoryService;

    public function __construct(BudgetService $budgetService, ProductCategoryService $categoryService)
    {
        $this->budgetService = $budgetService;
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        // The list, with create / edit / delete in modals, is one Livewire screen.
        return view('crud.host', ['component' => 'budget.budget-manager']);
    }

    public function create()
    {
        // Sidebar "create" menu: opens the create modal on the management screen.
        return redirect()->route('budgets.index', ['create' => 1]);
    }

    public function store(Request $request)
    {
        try {
            $this->budgetService->store($request->all());
            return redirect()->route('budgets.index')->with('success', 'Budget defined successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Old edit URL: opens the edit modal on the management screen.
        return redirect()->route('budgets.index', ['edit' => (int) $id]);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->budgetService->update($request->all(), $id);
            return redirect()->route('budgets.index')->with('success', 'Budget updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->budgetService->destroy($id);
            return response()->json(['success' => true, 'message' => 'Budget deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
