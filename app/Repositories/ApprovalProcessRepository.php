<?php

namespace App\Repositories;

use App\Models\ApprovalProcess;
use App\Repositories\BaseRepository;

class ApprovalEntityRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'approval_entity_id',
        'role_id',
        'serial',
        'is_approver',
        'is_processor',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ApprovalProcess::class;
    }
}
