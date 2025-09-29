<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsApplicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_application', function (Blueprint $table) {
            $table->id();
            $table->string('schoolarship_category')->nullable();
            $table->string('applicant_name_en')->nullable();
            $table->string('applicant_name_bn')->nullable();
            $table->string('village')->nullable();
            $table->string('post_office')->nullable();
            $table->string('upazilla')->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('dob')->nullable();
            $table->bigInteger('dob_registration_n')->nullable();
            $table->string('class_name', 100)->nullable();
            $table->integer('roll')->nullable();
            $table->string('departments')->nullable();
            $table->string('year', 10)->nullable();
            $table->string('shift', 50)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('race', 50)->nullable();
            $table->string('father_name_en')->nullable();
            $table->string('father_name_bn')->nullable();
            $table->string('mother_name_en')->nullable();
            $table->string('mother_name_bn')->nullable();
            $table->string('father_nid', 20)->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_nid', 20)->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('guardian_name_en')->nullable();
            $table->string('guardian_name_bn')->nullable();
            $table->string('guardian_relation', 50)->nullable();
            $table->string('guardian_nid', 20)->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('school_name')->nullable();
            $table->string('institute')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students_application');
    }
}
