<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vehicle requisitions ("গাড়ী রিকুইজিশন ফরম"): a user asks for a vehicle; Super Admin / Admin
 * approve it with a vehicle, driver and fuel amount, or reject it.
 *
 * New table only; no existing table is changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_requisitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();          // applicant
            $table->string('applicant_name', 191)->nullable();       // as at the time of applying
            $table->string('applicant_designation', 191)->nullable();
            $table->string('purpose', 20);                            // official | personal
            $table->text('official_details')->nullable();            // সরকারী কাজের সংক্ষিপ্ত বিবরণ
            $table->string('destination', 255);                       // গন্তব্য
            $table->decimal('distance_km', 8, 2)->nullable();        // আনুমানিক দূরত্ব
            $table->time('return_time')->nullable();                 // গাড়ী ফেরত প্রদানের সময়
            $table->string('report_place', 255);                      // গাড়ী রিপোর্ট স্থান
            $table->date('report_date');
            $table->time('report_time')->nullable();
            $table->string('status', 20)->default('pending')->index(); // pending | approved | rejected
            // Filled in on approval
            $table->unsignedBigInteger('vehicle_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->decimal('fuel_amount', 10, 2)->nullable();
            $table->string('fuel_unit', 30)->nullable();
            $table->text('decision_note')->nullable();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_requisitions');
    }
};
