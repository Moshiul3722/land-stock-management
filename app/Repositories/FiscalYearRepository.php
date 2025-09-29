<?php

namespace App\Repositories;

use App\Models\FiscalYear;
use App\Repositories\BaseRepository;

class FiscalYearRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'name_bn',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return FiscalYear::class;
    }
}
