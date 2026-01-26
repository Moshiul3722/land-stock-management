<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class PurchaseTypeBudget extends Model
{
    // Define the table associated with the model (optional if it follows Laravel's naming convention)
    protected $table = 'ptbudgets';

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pt_id',
        'fiscal_year_id',
        'amount',
        'spent_amount',
        'remarks',
        'created_by',
        'updated_by'
    ];

    public function PurchaseType(): BelongsTo
    {
        return $this->belongsTo(ProductPurchaseType::class, 'pt_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id');
    }
}
