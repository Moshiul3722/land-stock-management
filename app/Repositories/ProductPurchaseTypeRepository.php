<?php

namespace App\Repositories;

use App\Models\ProductPurchaseType;
use App\Repositories\BaseRepository;

class ProductPurchaseTypeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'name',
        'description',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ProductPurchaseType::class;
    }
}
