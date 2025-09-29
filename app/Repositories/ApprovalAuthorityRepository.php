<?php

namespace App\Repositories;

use App\Models\ApprovalAuthority;
use App\Repositories\BaseRepository;

class ApprovalAuthorityRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'role_id', // reference to User Role
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
        return ApprovalAuthority::class;
    }
}
