<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            // 1. Drop the old string column (WARNING: Data loss if not backed up)
            $table->dropColumn('fiscal_year'); 
            
            // 2. Add the new Foreign Key
            $table->foreignId('fiscal_year_id')
                  ->after('category_id')
                  ->constrained('fiscal_years');

            // 3. Re-add Unique Constraint
            $table->unique(['category_id', 'fiscal_year_id']);
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropForeign(['fiscal_year_id']);
            $table->dropColumn('fiscal_year_id');
            $table->string('fiscal_year', 9);
        });
    }
};
