<?php

namespace App\Repositories;

use App\Models\RequisitionItem;
use App\Repositories\BaseRepository;

class RequisitionItemRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'requisition_id',
        'product_id',
        'quantity',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return RequisitionItem::class;
    }
}
