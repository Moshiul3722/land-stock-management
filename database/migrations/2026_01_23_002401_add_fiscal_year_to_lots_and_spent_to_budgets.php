<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Link Lots to Fiscal Year
        Schema::table('lots', function (Blueprint $table) {
            $table->foreignId('fiscal_year_id')->nullable()->after('lot_number')->constrained('fiscal_years');
        });

        // 2. Track Spending in Budgets
        Schema::table('budgets', function (Blueprint $table) {
            $table->decimal('spent_amount', 15, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeign(['fiscal_year_id']);
            $table->dropColumn('fiscal_year_id');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('spent_amount');
        });
    }
};
