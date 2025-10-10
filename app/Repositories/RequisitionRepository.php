<?php

namespace App\Repositories;

use App\Models\Requisition;
use App\Repositories\BaseRepository;

class RequisitionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'requested_by_user_id',
        'requisition_date',
        'status',
        'current_serial',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Requisition::class;
    }
}
