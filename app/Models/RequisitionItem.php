<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequisitionItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'requisition_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'requisition_id',
        'product_id',
        'quantity',
        'original_quantity',
        'lot_id',
        'lot_detail_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Get the requisition that this item belongs to.
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class, 'requisition_id');
    }

    /**
     * Get the product for this requisition item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the assigned lot for this requisition item.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class, 'lot_id');
    }

    /**
     * Get the specific lot detail (Source of Truth for Brand/Spec).
     */
    public function lotDetail(): BelongsTo
    {
        return $this->belongsTo(LotDetail::class, 'lot_detail_id');
    }
}
