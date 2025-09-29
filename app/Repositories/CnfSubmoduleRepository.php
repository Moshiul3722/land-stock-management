<?php

namespace App\Repositories;

use App\Models\CnfSubmodule;
use App\Repositories\BaseRepository;

class CnfSubmoduleRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'submodule_name_en',
        'submodule_name_bn',
        'module_id',
        'is_active',
        'is_null',
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
        return CnfSubmodule::class;
    }
}
