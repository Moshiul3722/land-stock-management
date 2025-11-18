<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'approval_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approval_entity_id',
        'requistion_id',
        'lot_id',
        'from_user_id',
        'to_user_id',
        'serial',
        'status',
        'comments',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Get the approval entity this detail belongs to.
     */
    public function approvalEntity(): BelongsTo
    {
        return $this->belongsTo(ApprovalEntity::class, 'approval_entity_id');
    }

    /**
     * Get the requisition associated with this approval detail.
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class, 'requistion_id');
    }

    /**
     * Get the lot associated with this approval detail.
     */
    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class, 'lot_id');
    }

    /**
     * Get the user who sent the approval request.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who is assigned to action the approval.
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
