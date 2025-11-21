<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notifications';


    protected $fillable = [
        'user_from_id',
        'user_to_id',
        'notification_text',
        'url',
        'is_read',
        'read_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function userFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_from_id');
    }

    public function userTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_to_id');
    }
}
