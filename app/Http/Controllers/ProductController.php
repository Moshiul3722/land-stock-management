<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductCategoryService;
use App\Services\ProductPurchaseTypeService;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $productService;
    protected $productCategoryService;
    protected $productPurchaseTypeService;

    public function __construct(
        ProductService $productService,
        ProductCategoryService $productCategoryService,
        ProductPurchaseTypeService $productPurchaseTypeService
    ) {
        $this->productService = $productService;
        $this->productCategoryService = $productCategoryService;
        $this->productPurchaseTypeService = $productPurchaseTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // For AJAX requests (filtering)
        if ($request->ajax()) {
            $categoryId = $request->input('category_id');
            if ($categoryId) {
                $products = Product::with(['category', 'purchaseType'])->where('category_id', $categoryId)->get();
            } else {
                $products = Product::with(['category', 'purchaseType'])->get();
            }
            return view('product._product_list', compact('products'));
        }

        // For initial page load
        $categories = $this->productCategoryService->fetchAll();
        return view('product.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();
        return view('product.create', compact('categories', 'purchaseTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->productService->store($request->all());
            return redirect()->route('products.index')->with('success', 'Product created successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Load relationships for the single product
        $product->load(['category', 'purchaseType']);
        return view('product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = $this->productCategoryService->fetchAll();
        $purchaseTypes = $this->productPurchaseTypeService->fetchAll();
        return view('product.edit', compact('product', 'categories', 'purchaseTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $this->productService->update($request->all(), $product->id);
            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $this->productService->destroy($product->id);
            // Returning a JSON response for AJAX calls
            return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            // Returning a JSON error response
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
