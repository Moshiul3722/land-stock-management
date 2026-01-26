<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ptbudgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pt_id')->constrained('product_purchase_types')->onDelete('cascade');
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();

            // Standard audit columns
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptbudgets');
    }
};
