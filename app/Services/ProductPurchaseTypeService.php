<?php

namespace App\Services;

use App\Repositories\ProductPurchaseTypeRepository;

class ProductPurchaseTypeService
{
    /**
     * @var ProductPurchaseTypeRepository
     */
    protected $productPurchaseTypeRepository;

    /**
     * ProductPurchaseTypeService constructor.
     *
     * @param ProductPurchaseTypeRepository $productPurchaseTypeRepository
     */
    public function __construct(ProductPurchaseTypeRepository $productPurchaseTypeRepository)
    {
        $this->productPurchaseTypeRepository = $productPurchaseTypeRepository;
    }

    /**
     * Get all product purchase types.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll()
    {
        return $this->productPurchaseTypeRepository->all();
    }
}
