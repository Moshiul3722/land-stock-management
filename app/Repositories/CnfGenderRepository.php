<?php

namespace App\Repositories;

use App\Models\CnfGender;
use App\Repositories\BaseRepository;

class CnfGenderRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'gender_en',
        'gender_bn'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CnfGender::class;
    }
}
