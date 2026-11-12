<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A unit of measure offered when adding a product ("Pieces", "Box", ...).
 * Products store the unit's name in `products.product_unit`.
 */
class ProductUnit extends Model
{
    use SoftDeletes;

    protected $table = 'product_units';

    protected $fillable = ['name', 'created_by', 'updated_by', 'deleted_by'];
}
