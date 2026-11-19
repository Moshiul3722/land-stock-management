<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle management (vehicle_officers). */
class VehicleOfficer extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_officers';

    protected $fillable = ['name', 'name_bn', 'designation', 'department_id', 'mobile', 'created_by', 'updated_by', 'deleted_by'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_officer', 'officer_id', 'vehicle_id')->withTimestamps();
    }

    /** Name as shown on screen: Bangla on Bangla pages. */
    public function getLabelAttribute(): string
    {
        return app()->getLocale() === 'bn' && trim((string) $this->name_bn) !== '' ? $this->name_bn : (string) $this->name;
    }
}
