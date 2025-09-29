<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CnfPermission extends Model
{
    use SoftDeletes;

    public $table = 'cnf_permission';

    public $fillable = [
        'role_id',
        'menu_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'role_id' => 'nullable',
        'menu_id' => 'nullable',
        'created_at' => 'required',
        'created_by' => 'nullable',
        'updated_at' => 'required',
        'updated_by' => 'nullable',
        'deleted_at' => 'required',
        'deleted_by' => 'nullable'
    ];

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CnfRole::class, 'role_id');
    }

    public function menu(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CnfMenu::class, 'menu_id');
    }
}
