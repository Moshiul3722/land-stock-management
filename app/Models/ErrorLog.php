<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    // protected $connection = 'errorLog_mysql';

    protected $fillable = [
        'type',
        'message',
        'method',
        'created_at'
    ];
}
