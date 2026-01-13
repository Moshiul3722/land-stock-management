<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use App\Services\ProductSubCategoryService;
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
     * @var ProductSubCategoryService
     */
    protected $productSubCategoryService;

    /**
     * CategoryController constructor.
     *
     * @param ProductCategoryService $productCategoryService
     * @param ProductSubCategoryService $productSubCategoryService
     */
    public function __construct(
        ProductCategoryService $productCategoryService,
        ProductSubCategoryService $productSubCategoryService
    ) {
        $this->productCategoryService = $productCategoryService;
        $this->productSubCategoryService = $productSubCategoryService;
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
// ==========================================
    // Sub Category Methods
    // ==========================================

    /**
     * Display a listing of the sub categories.
     */
    public function subcategoryIndex()
    {
        // Use the SubCategory service to fetch all
        $subCategories = $this->productSubCategoryService->fetchAll();
        // Assuming view location is 'product_sub_category.index' based on previous context
        return view('sub_category.index', compact('subCategories'));
    }

    /**
     * Show the form for creating a new sub category.
     */
    public function subcategoryCreate()
    {
        // We need parent categories for the dropdown
        $categories = $this->productCategoryService->fetchAll();
        return view('sub_category.create', compact('categories'));
    }

    /**
     * Store a newly created sub category in storage.
     */
    public function subcategoryStore(Request $request)
    {
        try {
            // Use the SubCategory service to store
            $this->productSubCategoryService->store($request->all());
            return redirect()->route('subcategories.index')->with('success', 'Sub Category created successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified sub category.
     */
    public function subcategoryEdit($id)
    {
        try {
            $subCategory = $this->productSubCategoryService->findById($id);
            $categories = $this->productCategoryService->fetchAll();
            return view('sub_category.edit', compact('subCategory', 'categories'));
        } catch (Exception $e) {
            return redirect()->route('subcategories.index')->with('error', 'Sub Category not found.');
        }
    }

    /**
     * Update the specified sub category in storage.
     */
    public function subcategoryUpdate(Request $request, $id)
    {
        try {
            $this->productSubCategoryService->update($request->all(), $id);
            return redirect()->route('subcategories.index')->with('success', 'Sub Category updated successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified sub category from storage.
     */
    public function subcategoryDestroy($id)
    {
        try {
            $this->productSubCategoryService->destroy($id);
            return response()->json(['success' => true, 'message' => 'Sub Category deleted successfully.']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Get sub-categories by category ID for AJAX requests.
     * * @param Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubCategories(Request $request, $categoryId)
    {
        if ($request->ajax()) {
            // Fetch sub-categories using the service
            $subCategories = $this->productSubCategoryService->fetchByCategoryId($categoryId);
            return response()->json($subCategories);
        }
        
        abort(403, 'This action is unauthorized.');
    }
}
