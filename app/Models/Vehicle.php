<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle management (vehicles). */
class Vehicle extends Model
{
    use SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = ['vehicle_no', 'vehicle_type_id', 'driver_id', 'driving_date', 'route_id', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['driving_date' => 'date'];

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function driver()
    {
        return $this->belongsTo(VehicleDriver::class, 'driver_id');
    }

    public function route()
    {
        return $this->belongsTo(VehicleRoute::class, 'route_id');
    }

    /** Officers / offices the vehicle serves */
    public function officers()
    {
        return $this->belongsToMany(VehicleOfficer::class, 'vehicle_officer', 'vehicle_id', 'officer_id')->withTimestamps();
    }
}
