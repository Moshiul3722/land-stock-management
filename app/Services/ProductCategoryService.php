<?php

namespace App\Services;

use App\Repositories\ProductCategoryRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ProductCategoryService
{
    /**
     * @var ProductCategoryRepository
     */
    protected $productCategoryRepository;

    /**
     * ProductCategoryService constructor.
     *
     * @param ProductCategoryRepository $productCategoryRepository
     */
    public function __construct(ProductCategoryRepository $productCategoryRepository)
    {
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * Get all product categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->productCategoryRepository->all();
    }

    /**
     * Get a single product category by its ID.
     *
     * @param int $id
     * @return \App\Models\ProductCategory
     */
    public function findById($id)
    {
        return $this->productCategoryRepository->find($id);
    }

    /**
     * Validate and create a new product category.
     *
     * @param array $data
     * @return \App\Models\ProductCategory
     * @throws \Exception
     */
    public function store(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:product_categories',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $category = $this->productCategoryRepository->create($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to create product category');
        }
        DB::commit();

        return $category;
    }

    /**
     * Validate and update an existing product category.
     *
     * @param array $data
     * @param int $id
     * @return \App\Models\ProductCategory
     * @throws \Exception
     */
    public function update(array $data, $id)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:product_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $category = $this->productCategoryRepository->update($data, $id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to update product category');
        }
        DB::commit();

        return $category;
    }

    /**
     * Delete a product category by its ID.
     *
     * @param int $id
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->productCategoryRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to delete product category');
        }
        DB::commit();

        return $result;
    }
}