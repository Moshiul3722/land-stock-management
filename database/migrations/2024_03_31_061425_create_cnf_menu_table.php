<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnfMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnf_menu', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('submodule_id')->nullable();
            $table->string('menu_name_en')->nullable();
            $table->string('menu_name_bn')->nullable();
            $table->string('controller')->nullable();
            $table->string('action')->nullable();
            $table->string('route')->nullable();
            $table->string('is_menu')->nullable()->default(1);
            $table->integer('is_active')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('submodule_id', 'cnf_menu_ibfk_1')->references('id')->on('cnf_submodule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnf_menu');
    }
}
