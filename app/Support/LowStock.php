<?php

namespace App\Support;

use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Products whose total stock is below the limit: the scrolling "low stock" line under the top
 * bar, its list dialog and its printed list. Seen by Super Admin, Admin and Store Admin only.
 */
class LowStock
{
    public const ROLES = [1, 2, 3];

    public const LIMIT = 5;

    public static function canSee(?User $user): bool
    {
        return $user && in_array((int) $user->role_id, self::ROLES, true);
    }

    /**
     * Total stock of each product that has ever been stocked (same rows as the stock list),
     * lowest first: id, name (in the page language), unit, total, out (nothing left).
     *
     * @return Collection<int, array{id: int, name: string, unit: string, total: int, out: bool}>
     */
    public static function items(): Collection
    {
        $isBn = app()->getLocale() === 'bn';

        return DB::table('stocks')
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->whereNull('stocks.deleted_at')
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.name', 'products.name_bn', 'products.product_unit')
            ->havingRaw('SUM(stocks.quantity) < ?', [self::LIMIT])
            ->orderByRaw('SUM(stocks.quantity)')
            ->orderBy('products.name')
            ->selectRaw('products.id, products.name, products.name_bn, products.product_unit, SUM(stocks.quantity) as total')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $isBn && trim((string) $row->name_bn) !== '' ? $row->name_bn : $row->name,
                'unit' => $row->product_unit && ! is_numeric($row->product_unit) ? ProductUnit::label($row->product_unit) : '',
                'total' => max(0, (int) $row->total),
                'out' => (int) $row->total <= 0,
            ]);
    }

    public static function digits($value): string
    {
        return app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;
    }
}
