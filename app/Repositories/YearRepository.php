<?php

namespace App\Repositories;

use App\Models\Year;
use App\Repositories\BaseRepository;

class YearRepository extends BaseRepository
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
        return Year::class;
    }
}
