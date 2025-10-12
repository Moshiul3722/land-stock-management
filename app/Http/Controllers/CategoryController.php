<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * @var ProductCategoryService
     */
    protected $productCategoryService;

    /**
     * CategoryController constructor.
     *
     * @param ProductCategoryService $productCategoryService
     */
    public function __construct(ProductCategoryService $productCategoryService)
    {
        $this->productCategoryService = $productCategoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->productCategoryService->fetchAll();
        return view('category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->productCategoryService->store($request->all());
            return redirect()->route('categories.index')->with('success', 'Product category created successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $category)
    {
        return view('category.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $category)
    {
        return view('category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $category)
    {
        try {
            $this->productCategoryService->update($request->all(), $category->id);
            return redirect()->route('categories.index')->with('success', 'Product category updated successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $category)
    {
        try {
            $this->productCategoryService->destroy($category->id);
            return redirect()->route('categories.index')->with('success', 'Product category deleted successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
