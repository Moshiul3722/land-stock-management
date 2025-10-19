<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lot_number',
        'purchase_date',
        'entry_date',
        'status',
        'remarks',
    ];

/**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'entry_date' => 'date',
    ];
    /**
     * Get the details for the lot.
     */
    public function lotDetails(): HasMany
    {
        return $this->hasMany(LotDetail::class, 'lot_id');
    }

    /**
     * Get all of the lot's approval details.
     */
    public function approvalDetails(): HasMany
    {
        return $this->hasMany(ApprovalDetail::class, 'lot_id');
    }
}
