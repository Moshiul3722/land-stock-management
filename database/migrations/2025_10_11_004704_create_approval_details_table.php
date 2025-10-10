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
        Schema::create('approval_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_entity_id')->constrained('approval_entities')->onDelete('cascade');
            $table->foreignId('requistion_id')->nullable()->constrained('requisitions')->onDelete('cascade');
            $table->foreignId('lot_id')->nullable()->constrained('lots')->onDelete('cascade');
            $table->foreignId('from_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('serial');
            $table->string('status')->default('pending');
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('approval_details');
    }
};