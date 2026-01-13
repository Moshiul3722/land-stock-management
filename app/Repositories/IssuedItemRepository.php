<?php

namespace App\Repositories;

use App\Models\IssuedItem;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class IssuedItemRepository extends BaseRepository
{
    protected $fieldSearchable = ['user_id', 'product_id', 'requisition_id', 'lot_id'];
    public function getFieldsSearchable(): array { return $this->fieldSearchable; }
    public function model(): string { return IssuedItem::class; }

    public function getAllForUser(int $userId) { 
        return $this->model->where('user_id', $userId)
            ->with([
                'product.category',
                'lot.purchaseType',
                'requisition',
            ])
            ->orderBy('issued_date', 'desc')
            ->get();
    } 

    /**
     * Get detailed consumption report listing each item request separately.
     * Only includes requisitions that have been marked as 'completed' (acknowledged by user).
     * * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getConsumptionReport(array $filters = [])
    {
        $query = IssuedItem::query()
            // Join Products & Categories
            ->join('products', 'issued_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            
            // Join Users & Departments
            ->join('users', 'issued_items.user_id', '=', 'users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')

            // JOIN Requisition Items to get "Requested Quantity"
            ->join('requisition_items', function($join) {
                $join->on('issued_items.requisition_id', '=', 'requisition_items.requisition_id')
                     ->on('issued_items.product_id', '=', 'requisition_items.product_id');
            })

            // JOIN Requisitions to filter by status
            ->join('requisitions', 'issued_items.requisition_id', '=', 'requisitions.id')
            ->where('requisitions.status', 'approved') // <--- FILTER ADDED HERE
            
            ->select(
                'issued_items.requisition_id',
                'issued_items.issued_date',
                'products.name as product_name',
                'product_categories.name as category_name',
                'departments.name as department_name',
                
                // Get original requested amount
                DB::raw('MAX(requisition_items.original_quantity) as requested_quantity'),
                
                // Sum issued quantities (handles split-lot issuance)
                DB::raw('SUM(issued_items.quantity) as received_quantity')
            )
            ->groupBy(
                'issued_items.requisition_id',
                'issued_items.product_id',
                'issued_items.issued_date',
                'products.name',
                'product_categories.name',
                'departments.name'
            )
            ->orderBy('issued_items.issued_date', 'desc');

        // Apply Filters
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('issued_items.product_id', $filters['product_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->where('users.department_id', $filters['department_id']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('issued_items.issued_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->get();
    }
}