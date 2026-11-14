<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product units get a Bangla name next to the English one.
 *
 * Adds one optional column (product_units.name_bn). Existing rows keep their data; the
 * English name stays the value products refer to (products.product_unit).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_units', 'name_bn')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->string('name_bn', 191)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_units', 'name_bn')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->dropColumn('name_bn');
            });
        }
    }
};
