<?php

namespace App\Repositories;

use App\Models\LotDetail;
use App\Repositories\BaseRepository;

class LotDetailRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'lot_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return LotDetail::class;
    }
}
