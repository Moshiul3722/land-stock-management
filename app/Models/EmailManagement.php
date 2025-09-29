<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailManagement extends Model
{
    public $table = 'cnf_email_management';

    public $fillable = [
        'module_name',
        'status',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'module_name' => 'string'
    ];

    public static array $rules = [
        'module_name' => 'required|string|max:65535',
        'status' => 'required',
        'is_active' => 'required',
        'created_at' => 'required',
        'updated_at' => 'required',
        'created_by' => 'nullable',
        'updated_by' => 'nullable',
        'deleted_at' => 'required',
        'deleted_by' => 'nullable'
    ];

    
}
