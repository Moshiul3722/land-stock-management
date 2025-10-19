<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Services\LotService;
use App\Services\ProductCategoryService;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LotController extends Controller
{
    protected $lotService;
    protected $productService;
    protected $productCategoryService;

    public function __construct(LotService $lotService, ProductService $productService, ProductCategoryService $productCategoryService)
    {
        $this->lotService = $lotService;
        $this->productService = $productService;
        $this->productCategoryService = $productCategoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lots = $this->lotService->fetchAll();
        return view('lot.index', compact('lots'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->productCategoryService->fetchAll();
        return view('lot.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->lotService->store($request->all());
            return redirect()->route('lots.index')->with('success', 'Lot created and submitted for approval successfully.');
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
        $categories = $this->productCategoryService->fetchAll();
        $lot->load('lotDetails.product.category');
        return view('lot.edit', compact('lot', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lot $lot)
    {
        try {
            $this->lotService->update($request->all(), $lot->id);
            return redirect()->route('lots.index')->with('success', 'Lot updated successfully.');
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
}
