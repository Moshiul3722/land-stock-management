<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vehicle management: vehicle types, officers, drivers, routes, vehicles (with their officers)
 * and the drivers' demand forms.
 *
 * New tables only; no existing table is changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $audit = function (Blueprint $table) {
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        };

        Schema::create('vehicle_types', function (Blueprint $table) use ($audit) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_bn', 100);
            $audit($table);
        });

        Schema::create('vehicle_officers', function (Blueprint $table) use ($audit) {
            $table->id();
            $table->string('name', 150);
            $table->string('name_bn', 150)->nullable();
            $table->string('designation', 150)->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->string('mobile', 20)->nullable();
            $audit($table);
        });

        Schema::create('vehicle_drivers', function (Blueprint $table) use ($audit) {
            $table->id();
            // The driver's sign-in account (role "Vehicle Driver"), used for the demand form
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name', 150);
            $table->string('name_bn', 150)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('license_path')->nullable();
            $table->string('license_no', 50)->nullable();
            $audit($table);
        });

        Schema::create('vehicle_routes', function (Blueprint $table) use ($audit) {
            $table->id();
            $table->string('name', 150);
            $table->string('start_point', 150)->nullable();
            $table->string('end_point', 150)->nullable();
            $table->text('description')->nullable();
            $audit($table);
        });

        Schema::create('vehicles', function (Blueprint $table) use ($audit) {
            $table->id();
            $table->string('vehicle_no', 50);
            $table->unsignedBigInteger('vehicle_type_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->date('driving_date')->nullable();
            $table->unsignedBigInteger('route_id')->nullable()->index();
            $audit($table);
        });

        // Officers / offices a vehicle serves (more than one)
        Schema::create('vehicle_officer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('officer_id')->index();
            $table->timestamps();
            $table->unique(['vehicle_id', 'officer_id']);
        });

        Schema::create('vehicle_demands', function (Blueprint $table) use ($audit) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            // fuel, repair, parts, other
            $table->string('demand_type', 20);
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit', 30)->nullable();
            $table->text('reason')->nullable();
            $table->date('demand_date');
            $audit($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_demands');
        Schema::dropIfExists('vehicle_officer');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('vehicle_routes');
        Schema::dropIfExists('vehicle_drivers');
        Schema::dropIfExists('vehicle_officers');
        Schema::dropIfExists('vehicle_types');
    }
};
