<?php

namespace App\Services;

use App\Repositories\ProductSubCategoryRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ProductSubCategoryService
{
    /**
     * @var ProductSubCategoryRepository
     */
    protected $productSubCategoryRepository;

    /**
     * ProductSubCategoryService constructor.
     *
     * @param ProductSubCategoryRepository $productSubCategoryRepository
     */
    public function __construct(ProductSubCategoryRepository $productSubCategoryRepository)
    {
        $this->productSubCategoryRepository = $productSubCategoryRepository;
    }

    /**
     * Get all product sub categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->productSubCategoryRepository->all();
    }

    /**
     * Get sub categories by Parent Category ID.
     *
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchByCategoryId($categoryId)
    {
        return $this->productSubCategoryRepository->getByCategoryId($categoryId);
    }

    /**
     * Get a single product sub category by its ID.
     *
     * @param int $id
     * @return \App\Models\ProductSubCategory
     */
    public function findById($id)
    {
        return $this->productSubCategoryRepository->find($id);
    }

    /**
     * Validate and create a new product sub category.
     *
     * @param array $data
     * @return \App\Models\ProductSubCategory
     * @throws \Exception
     */
    public function store(array $data)
    {
        $validator = Validator::make($data, [
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $subCategory = $this->productSubCategoryRepository->create($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to create product sub category');
        }
        DB::commit();

        return $subCategory;
    }

    /**
     * Validate and update an existing product sub category.
     *
     * @param array $data
     * @param int $id
     * @return \App\Models\ProductSubCategory
     * @throws \Exception
     */
    public function update(array $data, $id)
    {
        $validator = Validator::make($data, [
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $subCategory = $this->productSubCategoryRepository->update($data, $id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to update product sub category');
        }
        DB::commit();

        return $subCategory;
    }

    /**
     * Delete a product sub category by its ID.
     *
     * @param int $id
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->productSubCategoryRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to delete product sub category');
        }
        DB::commit();

        return $result;
    }

    /**
     * Get the total number of sub categories.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->productSubCategoryRepository->count();
    }
}