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
        Schema::create('approval_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_entity_id')->constrained('approval_entities')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('cnf_role')->onDelete('cascade');
            $table->integer('serial');
            $table->boolean('is_approver')->default(true);
            $table->boolean('is_processor')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->unique(['approval_entity_id', 'serial']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_processes');
    }
};