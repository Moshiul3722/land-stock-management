<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnfMenu extends Model
{
    public $table = 'cnf_menu';

    public $fillable = [
        'submodule_id',
        'menu_name_en',
        'menu_name_bn',
        'controller',
        'action',
        'route',
        'is_menu',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'menu_name_en' => 'string',
        'menu_name_bn' => 'string',
        'controller' => 'string',
        'action' => 'string',
        'route' => 'string'
    ];

    public static array $rules = [
        'submodule_id' => 'nullable',
        'menu_name_en' => 'nullable|string|max:255',
        'menu_name_bn' => 'nullable|string|max:255',
        'controller' => 'nullable|string|max:255',
        'action' => 'nullable|string|max:255',
        'route' => 'nullable|string|max:255',
        'is_menu' => 'required',
        'is_active' => 'nullable',
        'created_at' => 'required',
        'created_by' => 'nullable',
        'updated_at' => 'required',
        'updated_by' => 'nullable',
        'deleted_at' => 'required',
        'deleted_by' => 'nullable'
    ];

    public function submodule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CnfSubmodule::class, 'submodule_id');
    }

    public function cnfPermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CnfPermission::class, 'menu_id');
    }
}
