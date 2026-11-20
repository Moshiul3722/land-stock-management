<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How far a vehicle of this type goes on one litre of fuel (km), used to work out the fuel
 * for a vehicle demand. Adds one empty (nullable) column; existing rows are not changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('vehicle_types', 'km_per_litre')) {
            Schema::table('vehicle_types', function (Blueprint $table) {
                $table->decimal('km_per_litre', 6, 2)->nullable()->after('name_bn');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicle_types', 'km_per_litre')) {
            Schema::table('vehicle_types', function (Blueprint $table) {
                $table->dropColumn('km_per_litre');
            });
        }
    }
};
