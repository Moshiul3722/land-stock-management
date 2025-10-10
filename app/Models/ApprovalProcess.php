<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalProcess extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'approval_processes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approval_entity_id',
        'role_id',
        'serial',
        'is_approver',
        'is_processor',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_approver' => 'boolean',
        'is_processor' => 'boolean',
    ];

    /**
     * Get the approval entity this process belongs to.
     */
    public function approvalEntity(): BelongsTo
    {
        return $this->belongsTo(ApprovalEntity::class, 'approval_entity_id');
    }

    /**
     * Get the role associated with this process step.
     */
    public function role(): BelongsTo
    {
        // Assuming you have a Role model for the 'cnf_role' table
        return $this->belongsTo(CnfRole::class, 'role_id');
    }
}