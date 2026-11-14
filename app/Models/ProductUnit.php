<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A unit of measure offered when adding a product ("Pieces", "Box", ...).
 * Products store the unit's (English) name in `products.product_unit`; `name_bn` is its Bangla name.
 */
class ProductUnit extends Model
{
    use SoftDeletes;

    protected $table = 'product_units';

    protected $fillable = ['name', 'name_bn', 'created_by', 'updated_by', 'deleted_by'];

    /** @var array<string, string>|null English name => Bangla name, loaded once per request. */
    private static ?array $banglaNames = null;

    /**
     * The unit name to show on screen: its Bangla name when the page is in Bangla and one is
     * set, otherwise the name as stored (products keep the English name in `product_unit`).
     */
    public static function label(?string $name): string
    {
        $name = (string) $name;
        if ($name === '' || app()->getLocale() !== 'bn') {
            return $name;
        }

        self::$banglaNames ??= static::query()
            ->whereNotNull('name_bn')
            ->where('name_bn', '!=', '')
            ->pluck('name_bn', 'name')
            ->all();

        return self::$banglaNames[$name] ?? $name;
    }

    /** Forget the cached names (after a unit is added, renamed or removed). */
    public static function flushLabels(): void
    {
        self::$banglaNames = null;
    }
}
