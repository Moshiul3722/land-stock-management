<?php

namespace App\Services;

use App\Repositories\StockRepository;

class StockService
{
    protected $stockRepository;

    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    /**
     * Get the aggregated stock list based on filters.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getAggregatedStockList(array $filters = [])
    {
        return $this->stockRepository->getAggregatedStock($filters);
    }

    /**
     * A product's current stock: all its stock rows added up (every lot, purchase type, brand
     * and specification), the same total the approval dialog and the stock list show.
     */
    public function availableQuantity(int $productId): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('stocks')
            ->where('product_id', $productId)
            ->whereNull('deleted_at')
            ->sum('quantity');
    }
}
