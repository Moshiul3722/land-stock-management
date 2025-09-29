<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeneficiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('year_id')->nullable();
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->unsignedBigInteger('board_id')->nullable();
            $table->bigInteger('sl_no')->nullable();
            $table->unsignedBigInteger('sm_a_details_id')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('roll_no')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_name_bn')->nullable();
            $table->string('birth_reg_no')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('eiin')->nullable();
            $table->string('institute_name')->nullable();
            $table->unsignedBigInteger('institute_id')->nullable();
            $table->string('gpa')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('routing_no')->nullable();
            $table->string('account_no')->nullable();
            $table->unsignedBigInteger('account_type')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('email')->nullable();
            $table->string('father_nid_no')->nullable();
            $table->string('mother_nid_no')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->bigInteger('deleted_by')->nullable();
            
            $table->foreign('year_id', 'beneficiaries_ibfk_1')->references('id')->on('year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beneficiaries');
    }
}
