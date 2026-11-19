<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle management (vehicle_types). */
class VehicleType extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_types';

    protected $fillable = ['name', 'name_bn', 'created_by', 'updated_by', 'deleted_by'];

    /** Name as shown on screen: Bangla on Bangla pages. */
    public function getLabelAttribute(): string
    {
        return app()->getLocale() === 'bn' && trim((string) $this->name_bn) !== '' ? $this->name_bn : (string) $this->name;
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
