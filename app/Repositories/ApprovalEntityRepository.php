<?php

namespace App\Repositories;

use App\Models\ApprovalEntity;
use App\Repositories\BaseRepository;

class ApprovalEntityRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'name',
        'description',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ApprovalEntity::class;
    }
}
