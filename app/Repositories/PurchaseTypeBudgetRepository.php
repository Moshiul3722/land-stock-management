<?php

namespace App\Repositories;

use App\Models\PurchaseTypeBudget;
use App\Repositories\BaseRepository;

class PurchaseTypeBudgetRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'pt_id',
        'fiscal_year_id',
        'spent_amount',
        'amount',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return PurchaseTypeBudget::class;
    }

    /**
     * Get budgets with Purchase Type relationships
     */
    public function getAllWithPurchaseType()
    {
        return $this->model->with('PurchaseType')->orderBy('fiscal_year_id', 'desc')->get();
    }
}
