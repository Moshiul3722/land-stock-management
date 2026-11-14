<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'name_bn',
        'description',
        'category_id',
        'sub_category_id',
        'product_unit',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * Get the category that the product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    /**
     * Get the sub-category that the product belongs to.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubCategory::class, 'sub_category_id');
    }

    /**
     * Get all of the stock entries for this product.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

    /**
     * The product's unit as shown on screen (`$product->unit_label`): Bangla when the page is
     * in Bangla and the unit has a Bangla name, otherwise the stored unit name.
     */
    public function getUnitLabelAttribute(): string
    {
        return ProductUnit::label($this->product_unit);
    }
}
