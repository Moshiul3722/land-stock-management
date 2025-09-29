<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnfReligion extends Model
{
    public $table = 'cnf_religion';

    public $fillable = [
        'religion_en',
        'religion_bn'
    ];

    protected $casts = [
        'religion_en' => 'string',
        'religion_bn' => 'string'
    ];

    public static array $rules = [
        'religion_en' => 'nullable|string|max:255',
        'religion_bn' => 'nullable|string|max:255'
    ];

    
}
