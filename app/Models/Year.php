<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    public $table = 'year';

    public $fillable = [
        'name',
        'name_bn',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'name' => 'string',
        'name_bn' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'name_bn' => 'required|string|max:255',
        'created_at' => 'required',
        'updated_at' => 'required',
        'created_by' => 'nullable',
        'updated_by' => 'nullable',
        'deleted_at' => 'nullable',
        'deleted_by' => 'nullable'
    ];
}
