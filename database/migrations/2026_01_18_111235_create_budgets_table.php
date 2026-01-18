<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            $table->string('fiscal_year', 9); // Format: "2025-2026"
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();
            
            // Standard audit columns
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint to prevent duplicate budgets for same category/year
            $table->unique(['category_id', 'fiscal_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
