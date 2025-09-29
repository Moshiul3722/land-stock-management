<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnfEmailManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnf_email_management', function (Blueprint $table) {
            $table->id();
            $table->text('module_name')->nullable();
            $table->integer('status')->default(1);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('created_by', 'cnf_email_management_created_by_foreign')->references('id')->on('users');
            $table->foreign('deleted_by', 'cnf_email_management_deleted_by_foreign')->references('id')->on('users');
            $table->foreign('updated_by', 'cnf_email_management_updated_by_foreign')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnf_email_management');
    }
}
