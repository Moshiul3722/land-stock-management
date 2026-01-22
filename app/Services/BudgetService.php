<?php

namespace App\Services;

use App\Repositories\BudgetRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class BudgetService
{
    protected $budgetRepository;

    public function __construct(BudgetRepository $budgetRepository)
    {
        $this->budgetRepository = $budgetRepository;
    }

    public function fetchAll()
    {
        return $this->budgetRepository->getAllWithCategory();
    }

    public function findById($id)
    {
        return $this->budgetRepository->find($id);
    }

    /**
     * Store a new budget with duplicate checking.
     */
    public function store(array $data)
    {
        // 1. Basic Validation
        $validator = Validator::make($data, [
            'category_id' => 'required|exists:product_categories,id',
            'fiscal_year' => 'required|string|regex:/^\d{4}-\d{4}$/', 
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
        
        // 2. DUPLICATE CHECK: Check if budget already exists for this Category + Year
        $exists = $this->budgetRepository->allQuery()
            ->where('category_id', $data['category_id'])
            ->where('fiscal_year', $data['fiscal_year'])
            ->exists(); // Returns true/false
        
        if ($exists) {
            // This message will be caught by the Controller and sent to the View's SweetAlert
            throw new InvalidArgumentException("A budget for this category and fiscal year (" . $data['fiscal_year'] . ") already exists.");
        }

        $data['created_by'] = Auth::id();

        return DB::transaction(function () use ($data) {
            try {
                return $this->budgetRepository->create($data);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to create budget.');
            }
        });
    }

    public function update(array $data, int $id)
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $data['updated_by'] = Auth::id();

        return DB::transaction(function () use ($data, $id) {
            try {
                return $this->budgetRepository->update($data, $id);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to update budget.');
            }
        });
    }
    
    public function destroy($id)
    {
        return $this->budgetRepository->delete($id);
    }
}