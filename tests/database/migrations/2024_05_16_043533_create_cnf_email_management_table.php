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
            $table->integer('id')->primary();
            $table->text('module_name');
            $table->integer('status')->default(1);
            $table->integer('is_active')->default(1);
            $table->timestamps()->default('current_timestamp()');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->default('current_timestamp()');
            $table->bigInteger('deleted_by')->nullable();
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
