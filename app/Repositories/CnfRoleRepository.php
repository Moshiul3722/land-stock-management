<?php

namespace App\Repositories;

use App\Models\CnfRole;
use App\Repositories\BaseRepository;

/**
 * Class CnfRoleRepository
 * @package App\Repositories
 * @version March 28, 2024, 6:54 am UTC
*/

class CnfRoleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'role_name_en',
        'role_name_bn',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return CnfRole::class;
    }
}
