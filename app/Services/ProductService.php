<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ProductService
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->productRepository->all();
    }

    /**
     * Get a single product by its ID.
     *
     * @param int $id
     * @return \App\Models\Product
     */
    public function findById($id)
    {
        return $this->productRepository->find($id);
    }

    /**
     * Validate and create a new product.
     *
     * @param array $data
     * @return \App\Models\Product
     * @throws \Exception
     */
    public function store(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'product_unit' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepository->create($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to create product');
        }
        DB::commit();

        return $product;
    }

    /**
     * Validate and update an existing product.
     *
     * @param array $data
     * @param int $id
     * @return \App\Models\Product
     * @throws \Exception
     */
    public function update(array $data, $id)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:products,name,' . $id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'product_unit' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepository->update($data, $id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to update product');
        }
        DB::commit();

        return $product;
    }

    /**
     * Delete a product by its ID.
     *
     * @param int $id
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->productRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to delete product');
        }
        DB::commit();

        return $result;
    }

    /**
     * Get the total number of products.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->productRepository->count();
    }

    /**
     * Get all products belonging to a specific category.
     *
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByCategoryId($categoryId)
    {
        return $this->productRepository->findBy('category_id', $categoryId);
    }

    /**
     * Get suggestions for product units for autocomplete.
     */
    public function getUnitSuggestions(?string $term)
    {
        return DB::table('products')
            ->where('product_unit', 'LIKE', '%' . $term . '%')
            ->whereNotNull('product_unit')
            ->distinct()
            ->orderBy('product_unit')
            ->limit(10)
            ->pluck('product_unit');
    }
}
