<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserType;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'email',
        'role_id',
        'usertype_id',
        'department_id',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return User::class;
    }

    public function getAllUserTypes()
    {
        return UserType::all();
    }
}
