<?php

namespace App\Http\Controllers;

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
        $budgets = $this->budgetService->fetchAll();
        $categories = $this->categoryService->fetchAll();
        $defaultFiscalYear = date('Y') . '-' . (date('Y') + 1);
        return view('budget.index', compact('budgets', 'categories', 'defaultFiscalYear'));
    }

    public function create()
    {
        $categories = $this->categoryService->fetchAll();
        // Generate next fiscal year string (e.g., 2025-2026) as default
        $currentYear = date('Y');
        $defaultFiscalYear = $currentYear . '-' . ($currentYear + 1);
        
        return view('budget.create', compact('categories', 'defaultFiscalYear'));
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
        $budget = $this->budgetService->findById($id);
        $categories = $this->categoryService->fetchAll();
        return view('budget.edit', compact('budget', 'categories'));
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