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
}
