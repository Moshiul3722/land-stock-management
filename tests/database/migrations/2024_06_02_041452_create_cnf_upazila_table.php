<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnfUpazilaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnf_upazila', function (Blueprint $table) {
            $table->id();
            $table->string('upazila_name_en')->nullable();
            $table->string('upazila_name_bn')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('district_id', 'cnf_upazila_ibfk_1')->references('id')->on('cnf_district');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnf_upazila');
    }
}
