<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A product's Bangla name, next to the existing (English) name.
 * Optional: existing products keep working without it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'name_bn')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('name_bn', 191)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'name_bn')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('name_bn');
            });
        }
    }
};
