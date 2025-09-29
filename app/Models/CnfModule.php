<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnfModule extends Model
{
    public $table = 'cnf_module';

    public $fillable = [
        'module_name_en',
        'module_name_bn',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'module_name_en' => 'string',
        'module_name_bn' => 'string'
    ];

    public static array $rules = [
        'module_name_en' => 'nullable|string|max:255',
        'module_name_bn' => 'nullable|string|max:255',
        'is_active' => 'nullable',
        'created_at' => 'required',
        'created_by' => 'nullable',
        'updated_at' => 'required',
        'updated_by' => 'nullable',
        'deleted_at' => 'required',
        'deleted_by' => 'nullable'
    ];

    public function cnfSubmodules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CnfSubmodule::class, 'module_id');
    }
}
