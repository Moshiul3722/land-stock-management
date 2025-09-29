<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmADetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_a_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->unsignedBigInteger('fiscal_id')->nullable();
            $table->unsignedBigInteger('year_id')->nullable();
            $table->unsignedBigInteger('number')->nullable();
            $table->unsignedBigInteger('rate')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('type_id', 'sm_a_details_ibfk_1')->references('id')->on('sm_a_type');
            $table->foreign('exam_id', 'sm_a_details_ibfk_2')->references('id')->on('sm_a_exam');
            $table->foreign('fiscal_id', 'sm_a_details_ibfk_3')->references('id')->on('fiscal_year');
            $table->foreign('year_id', 'sm_a_details_ibfk_4')->references('id')->on('year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sm_a_details');
    }
}
