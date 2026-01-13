<?php

namespace App\Repositories;

use App\Models\ProductSubCategory;
use App\Repositories\BaseRepository;

class ProductSubCategoryRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'code',
        'category_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ProductSubCategory::class;
    }

    
    /**
     * Get sub categories by parent category ID.
     *
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCategoryId($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->get();
    }
}