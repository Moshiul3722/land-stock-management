<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle management (vehicle_routes). */
class VehicleRoute extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_routes';

    protected $fillable = ['name', 'start_point', 'end_point', 'description', 'created_by', 'updated_by', 'deleted_by'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'route_id');
    }
}
