<?php

namespace App\Repositories;

use App\Models\CnfModule;
use App\Repositories\BaseRepository;

class CnfModuleRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'module_name_en',
        'module_name_bn',
        'is_active',
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
        return CnfModule::class;
    }
}
