<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRsuGazetteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rsu_gazette', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sl_no')->nullable();
            $table->unsignedBigInteger('sm_a_type_id')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_name_bn')->nullable();
            $table->string('birth_reg_no')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('fathers_name')->nullable();
            $table->string('mothers_name')->nullable();
            $table->string('eiin')->nullable();
            $table->string('institute_name')->nullable();
            $table->unsignedBigInteger('institute_id')->nullable();
            $table->string('gpa')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('sm_a_type_id', 'rsu_gazette_ibfk_4')->references('id')->on('sm_a_type');
            $table->foreign('gender_id', 'rsu_gazette_ibfk_6')->references('id')->on('cnf_gender');
            $table->foreign('group_id', 'rsu_gazette_ibfk_7')->references('id')->on('rsu_group');
            $table->foreign('institute_id', 'rsu_gazette_ibfk_8')->references('id')->on('ims_institute');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rsu_gazette');
    }
}
