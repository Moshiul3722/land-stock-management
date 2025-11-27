<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lot_details', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('product_id');
            $table->string('specification')->nullable()->after('brand');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('product_id');
            $table->string('specification')->nullable()->after('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lot_details', function (Blueprint $table) {
            $table->dropColumn(['brand', 'specification']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['brand', 'specification']);
        });
    }
};
