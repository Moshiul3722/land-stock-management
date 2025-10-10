<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Repositories\BaseRepository;

class StockRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'product_id',
        'quantity',
        'lot_id',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Stock::class;
    }
}
