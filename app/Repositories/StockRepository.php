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
        'brand',
        'specification',
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
            // Join Sub Categories (Left Join because it's optional)
            ->leftJoin('product_sub_categories', 'products.sub_category_id', '=', 'product_sub_categories.id')
            
            ->leftJoin('lots', 'stocks.lot_id', '=', 'lots.id')
            ->leftJoin('product_purchase_types', 'lots.product_purchase_type_id', '=', 'product_purchase_types.id')

            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'product_categories.name as category_name',
                'product_sub_categories.name as sub_category_name', // <--- NEW
                'product_purchase_types.name as purchase_type_name',
                'stocks.brand',
                'stocks.specification',
                DB::raw('SUM(stocks.quantity) as total_quantity')
            )
            ->whereNull('stocks.deleted_at')
            
            ->groupBy(
                'products.id', 
                'products.name', 
                'product_categories.name',
                'product_sub_categories.name', // <--- NEW
                'product_purchase_types.id',
                'product_purchase_types.name',
                'stocks.brand',
                'stocks.specification'
            );

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        // Apply purchase type filter
        if (!empty($filters['purchase_type_id'])) {
            $query->where('lots.product_purchase_type_id', $filters['purchase_type_id']);
        }

        // Apply product filter
        if (!empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }

        // Period (stock report): only the stock entries made from / up to these dates count.
        // Dates are YYYY-MM-DD; anything else is ignored.
        foreach (['stock_date_from' => '>=', 'stock_date_to' => '<='] as $key => $operator) {
            $date = $filters[$key] ?? null;
            if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $query->whereDate('stocks.created_at', $operator, $date);
            }
        }

        return $query->get();
    }
}
