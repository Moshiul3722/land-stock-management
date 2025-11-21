<?php

namespace App\Repositories;

use App\Models\IssuedItem;
use App\Repositories\BaseRepository;

class IssuedItemRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'user_id',
        'requisition_id',
        'product_id',
        'lot_id',
        'quantity',
        'issued_date',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return IssuedItem::class;
    }

    /**
     * Get all items issued to a specific user with full details.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllForUser(int $userId)
    {
        return $this->model->where('user_id', $userId)
            ->with([
                'product.category',       // To get Product Category
                'lot.purchaseType',       // To get Purchase Type (via Lot)
                'requisition',            // To get Requisition Date
            ])
            ->orderBy('issued_date', 'desc')
            ->get();
    }
}