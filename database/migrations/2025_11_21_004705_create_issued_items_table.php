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
        Schema::create('issued_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Mr. X
            $table->foreignId('requisition_id')->constrained('requisitions'); // The request reference
            $table->foreignId('product_id')->constrained('products'); // Product
            $table->foreignId('lot_id')->constrained('lots'); // The specific Lot
            $table->integer('quantity'); // Quantity
            $table->date('issued_date');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issued_items');
    }
};
