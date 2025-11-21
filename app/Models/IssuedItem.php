<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requisition_id',
        'product_id',
        'lot_id',
        'quantity',
        'issued_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}
