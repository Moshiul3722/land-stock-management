<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnfSubmoduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnf_submodule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('submodule_name_en')->nullable();
            $table->string('submodule_name_bn')->nullable();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->unsignedBigInteger('is_active')->nullable();
            $table->unsignedBigInteger('is_null')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('module_id', 'cnf_submodule_ibfk_1')->references('id')->on('cnf_module');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnf_submodule');
    }
}
