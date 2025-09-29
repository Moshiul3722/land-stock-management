<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class form_status extends Model
{
    use HasFactory;
    protected $table = 'form_statuses';
    protected $fillable = [
        'form_id',
        'form_type',
        'uid',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

}
