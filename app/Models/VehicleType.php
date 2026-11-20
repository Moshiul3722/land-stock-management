<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle management (vehicle_types). */
class VehicleType extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_types';

    protected $fillable = ['name', 'name_bn', 'km_per_litre', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = ['km_per_litre' => 'decimal:2'];

    /** Usual km per litre by kind of vehicle (found in the type's name), when none is set */
    private const USUAL_KM_PER_LITRE = [
        35 => ['motorcycle', 'motor cycle', 'bike', 'মোটরসাইকেল', 'মোটর সাইকেল', 'বাইক'],
        8 => ['microbus', 'micro bus', 'hiace', 'haiace', 'noah', 'মাইক্রো', 'হায়েস', 'হাইয়েস', 'নোয়া', 'pickup', 'pick up', 'double cabin', 'পিকআপ', 'পিক আপ', 'ডাবল কেবিন'],
        7 => ['jeep', 'pajero', 'prado', 'land cruiser', 'suv', 'জীপ', 'জিপ', 'পাজেরো', 'প্রাডো'],
        10 => ['car', 'sedan', 'premio', 'allion', 'corolla', 'axio', 'কার', 'সেডান', 'প্রিমিও', 'এলিয়ন', 'করোলা'],
        4 => ['bus', 'truck', 'coaster', 'বাস', 'ট্রাক', 'কোস্টার'],
    ];

    /**
     * Km per litre used for the fuel of a demand: the value set on the type, else the usual
     * value for its kind (from its name), else null.
     *
     * @return array{value: float, usual: bool}|null
     */
    public function fuelEconomy(): ?array
    {
        if ($this->km_per_litre !== null && (float) $this->km_per_litre > 0) {
            return ['value' => (float) $this->km_per_litre, 'usual' => false];
        }
        $name = mb_strtolower($this->name . ' ' . $this->name_bn);
        foreach (self::USUAL_KM_PER_LITRE as $km => $words) {
            foreach ($words as $word) {
                if (str_contains($name, mb_strtolower($word))) {
                    return ['value' => (float) $km, 'usual' => true];
                }
            }
        }

        return null;
    }

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
