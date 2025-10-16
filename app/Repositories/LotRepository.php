<?php

namespace App\Repositories;

use App\Models\Lot;
use App\Repositories\BaseRepository;

class LotRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'lot_number',
        'purchase_date',
        'entry_date',
        'status',
        'remarks',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Lot::class;
    }
}
