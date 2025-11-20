<?php

namespace App\Repositories;

use App\Models\ApprovalDetail;
use App\Repositories\BaseRepository;

class ApprovalDetailRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'approval_entity_id',
        'requistion_id',
        'lot_id',
        'from_user_id',
        'to_user_id',
        'serial',
        'status',
        'comments',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ApprovalDetail::class;
    }
}
