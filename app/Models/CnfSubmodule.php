<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnfSubmodule extends Model
{
    public $table = 'cnf_submodule';

    public $fillable = [
        'submodule_name_en',
        'submodule_name_bn',
        'module_id',
        'is_null',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'submodule_name_en' => 'string',
        'submodule_name_bn' => 'string'
    ];

    public static array $rules = [
        'submodule_name_en' => 'nullable|string|max:255',
        'submodule_name_bn' => 'nullable|string|max:255',
        'module_id' => 'nullable',
        'is_active' => 'nullable',
        'is_null' => 'nullable',
        'created_at' => 'required',
        'created_by' => 'nullable',
        'updated_at' => 'required',
        'updated_by' => 'nullable',
        'deleted_at' => 'required',
        'deleted_by' => 'nullable'
    ];

    public function module(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CnfModule::class, 'module_id');
    }

    public function cnfMenus(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CnfMenu::class, 'submodule_id');
    }
}
