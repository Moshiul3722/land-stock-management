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
     * Get an aggregated list of stock, grouped by product and purchase type.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getAggregatedStock(array $filters = [])
    {
        $query = Stock::query()
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            // Join Lots and Purchase Types to allow grouping
            ->leftJoin('lots', 'stocks.lot_id', '=', 'lots.id')
            ->leftJoin('product_purchase_types', 'lots.product_purchase_type_id', '=', 'product_purchase_types.id')

            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_categories.name as category_name',
                'product_purchase_types.name as purchase_type_name',
                DB::raw('SUM(stocks.quantity) as total_quantity')
            )
            ->whereNull('stocks.deleted_at')
            // Group by Product AND Purchase Type
            ->groupBy(
                'products.id', 
                'products.name', 
                'product_categories.name',
                'product_purchase_types.id',
                'product_purchase_types.name'
            );

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        // Apply purchase type filter (conditionally add the join)
        if (!empty($filters['purchase_type_id'])) {
            $query->join('lots', 'stocks.lot_id', '=', 'lots.id')
                ->where('lots.product_purchase_type_id', $filters['purchase_type_id']);
        }

        // Apply product filter
        if (!empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }

        return $query->get();
    }
}
