<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

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

        /**
     * Get an aggregated list of stock, grouped by product, with filters.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getAggregatedStock(array $filters = [])
    {
        $query = Stock::query()
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->join('product_purchase_types', 'products.purchase_type_id', '=', 'product_purchase_types.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_categories.name as category_name',
                'product_purchase_types.name as purchase_type_name',
                DB::raw('SUM(stocks.quantity) as total_quantity')
            )
            ->whereNull('stocks.deleted_at') // Only count non-deleted stock
            ->groupBy('products.id', 'products.name', 'product_categories.name', 'product_purchase_types.name');

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['purchase_type_id'])) {
            $query->where('products.purchase_type_id', $filters['purchase_type_id']);
        }

        return $query->get();
    }
}
