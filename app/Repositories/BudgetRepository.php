<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Repositories\BaseRepository;

class BudgetRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'category_id',
        'fiscal_year_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Budget::class;
    }
    
    /**
     * Get budgets with category relationships
     */
    public function getAllWithCategory()
    {
        return $this->model->with('category')->orderBy('fiscal_year_id', 'desc')->get();
    }
}