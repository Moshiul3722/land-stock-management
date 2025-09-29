<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnfBankBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cnf_bank_branch', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name_en')->nullable();
            $table->string('branch_name_bn')->nullable();
            $table->string('routing_no')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('bank_dist_id')->nullable();
            $table->unsignedBigInteger('is_active')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->foreign('bank_id', 'cnf_bank_branch_ibfk_1')->references('id')->on('cnf_bank');
            $table->foreign('bank_dist_id', 'cnf_bank_branch_ibfk_2')->references('id')->on('cnf_bank_district');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cnf_bank_branch');
    }
}
