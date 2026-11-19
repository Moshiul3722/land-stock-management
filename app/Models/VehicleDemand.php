<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle management (vehicle_demands). */
class VehicleDemand extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_demands';

    protected $fillable = ['vehicle_id', 'driver_id', 'demand_type', 'quantity', 'unit', 'reason', 'demand_date', 'created_by', 'updated_by', 'deleted_by'];

    /** What a driver can ask for */
    public const TYPES = ['fuel', 'repair', 'parts', 'other'];

    protected $casts = ['demand_date' => 'date', 'quantity' => 'decimal:2'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    public function driver()
    {
        return $this->belongsTo(VehicleDriver::class, 'driver_id')->withTrashed();
    }
}
