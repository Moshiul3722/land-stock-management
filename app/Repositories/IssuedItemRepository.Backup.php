<?php

namespace App\Repositories;

use App\Models\IssuedItem;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get aggregated consumption report with filters.
     * Added Requested vs Received count
     * * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getConsumptionReport(array $filters = [])
    {
        $query = IssuedItem::query()
            // Join Products & Categories
            ->join('products', 'issued_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')

            // Join Lots & Purchase Types
            ->join('lots', 'issued_items.lot_id', '=', 'lots.id')
            ->join('product_purchase_types', 'lots.product_purchase_type_id', '=', 'product_purchase_types.id')

            // JOIN lot_details to get the Brand/Spec of the issued item
            ->join('lot_details', function ($join) {
                $join->on('issued_items.lot_id', '=', 'lot_details.lot_id')
                    ->on('issued_items.product_id', '=', 'lot_details.product_id');
            })

            // JOIN Requisition Items to get "Requested Quantity"
            ->join('requisition_items', function ($join) {
                $join->on('issued_items.requisition_id', '=', 'requisition_items.requisition_id')
                    ->on('issued_items.product_id', '=', 'requisition_items.product_id');
            })

            // Join Users & Departments
            ->join('users', 'issued_items.user_id', '=', 'users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')

            ->select(
                'products.name as product_name',
                'product_categories.name as category_name',
                'product_purchase_types.name as purchase_type_name',
                'departments.name as department_name',
                'lot_details.brand',
                'lot_details.specification',
                DB::raw('MAX(requisition_items.original_quantity) as requested_quantity'),

                DB::raw('SUM(issued_items.quantity) as received_quantity')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'product_categories.name',
                'product_purchase_types.name',
                'departments.name',
                'lot_details.brand',
                'lot_details.specification',
                'issued_items.requisition_id',
                'issued_items.product_id',
                'issued_items.issued_date',
            );

        // Apply Filters
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('issued_items.product_id', $filters['product_id']);
        }
        if (!empty($filters['purchase_type_id'])) {
            $query->where('lots.product_purchase_type_id', $filters['purchase_type_id']);
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
