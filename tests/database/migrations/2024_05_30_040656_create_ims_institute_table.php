<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImsInstituteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ims_institute', function (Blueprint $table) {
            $table->id();
            $table->string('institute_name_en')->nullable();
            $table->string('institute_name_bn')->nullable();
            $table->string('eiin')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('management_id')->nullable();
            $table->unsignedBigInteger('level_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('upazila_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('is_active')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();
            
            $table->foreign('category_id', 'ims_institute_ibfk_1')->references('id')->on('ims_ins_category');
            $table->foreign('management_id', 'ims_institute_ibfk_2')->references('id')->on('ims_ins_management');
            $table->foreign('level_id', 'ims_institute_ibfk_3')->references('id')->on('ims_ins_level');
            $table->foreign('type_id', 'ims_institute_ibfk_4')->references('id')->on('ims_ins_type');
            $table->foreign('upazila_id', 'ims_institute_ibfk_5')->references('id')->on('cnf_upazila');
            $table->foreign('district_id', 'ims_institute_ibfk_6')->references('id')->on('cnf_district');
            $table->foreign('division_id', 'ims_institute_ibfk_7')->references('id')->on('cnf_division');
            $table->foreign('zone_id', 'ims_institute_ibfk_8')->references('id')->on('cnf_zone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ims_institute');
    }
}
