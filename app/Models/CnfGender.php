<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CnfGender extends Model
{
    public $table = 'cnf_gender';

    public $fillable = [
        'gender_en',
        'gender_bn'
    ];

    protected $casts = [
        'gender_en' => 'string',
        'gender_bn' => 'string'
    ];

    public static array $rules = [
        'gender_en' => 'nullable|string|max:255',
        'gender_bn' => 'nullable|string|max:255'
    ];

    
}
