<?php

namespace App\Repositories;

use App\Models\EmailManagement;
use App\Repositories\BaseRepository;

class EmailManagementRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'module_name',
        'status',
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
        return EmailManagement::class;
    }
}
