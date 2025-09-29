<?php

namespace App\Repositories;

use App\Models\Approval;
use App\Repositories\BaseRepository;

class ApprovalRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'form_master_id', // reference to Form Master Model
        'user_from', // reference to User
        'district_id', // Referencce to User
        'status', // Status codes: 0 (local save), 1 (submitted to CME), 2 (submitted to MnE), 3 (approved)
        'is_active', // To mark current active status
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
        return Approval::class;
    }
}
