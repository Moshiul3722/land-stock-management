<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Gives the standard product units their Bangla name.
 *
 * Data only: fills product_units.name_bn where it is still empty. A Bangla name that was
 * already entered by hand is left alone; nothing else is touched.
 */
return new class extends Migration
{
    private const NAMES = [
        'Bottle' => 'বোতল',
        'Box' => 'বক্স',
        'Bundle' => 'বান্ডিল',
        'Dozen' => 'ডজন',
        'Packet' => 'প্যাকেট',
        'Pieces' => 'পিস',
        'Rim' => 'রিম',
        'Roll' => 'রোল',
    ];

    public function up(): void
    {
        foreach (self::NAMES as $name => $bangla) {
            DB::table('product_units')
                ->where('name', $name)
                ->where(fn ($query) => $query->whereNull('name_bn')->orWhere('name_bn', ''))
                ->update(['name_bn' => $bangla]);
        }
    }

    public function down(): void
    {
        foreach (self::NAMES as $name => $bangla) {
            DB::table('product_units')->where('name', $name)->where('name_bn', $bangla)->update(['name_bn' => null]);
        }
    }
};
