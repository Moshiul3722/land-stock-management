<?php

namespace App\Repositories;

use App\Models\CnfMenu;
use App\Repositories\BaseRepository;

class CnfMenuRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'submodule_id',
        'menu_name_en',
        'menu_name_bn',
        'controller',
        'action',
        'route',
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
        return CnfMenu::class;
    }
}
