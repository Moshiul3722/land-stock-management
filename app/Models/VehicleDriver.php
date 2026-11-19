<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle management (vehicle_drivers). */
class VehicleDriver extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_drivers';

    protected $fillable = ['user_id', 'name', 'name_bn', 'mobile', 'email', 'photo_path', 'license_path', 'license_no', 'created_by', 'updated_by', 'deleted_by'];

    /** Id of the "Vehicle Driver" role (0 when it does not exist). */
    public static function roleId(): int
    {
        static $id = null;

        return $id ??= (int) \Illuminate\Support\Facades\DB::table('cnf_role')
            ->where('role_name_en', 'Vehicle Driver')->whereNull('deleted_at')->value('id');
    }

    /** The driver's sign-in account (role "Vehicle Driver"). */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'driver_id');
    }

    /** Name as shown on screen: Bangla on Bangla pages. */
    public function getLabelAttribute(): string
    {
        return app()->getLocale() === 'bn' && trim((string) $this->name_bn) !== '' ? $this->name_bn : (string) $this->name;
    }
}
