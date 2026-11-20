<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Vehicle requisition (গাড়ী রিকুইজিশন ফরম). */
class VehicleRequisition extends Model
{
    use SoftDeletes;

    public const PURPOSES = ['official', 'personal'];

    /** Fuel units an approver can choose (stored as written here) */
    public const FUEL_UNITS = ['লিটার', 'মিলিলিটার', 'গ্যালন', 'ব্যারেল', 'ঘনমিটার', 'কেজি'];

    protected $table = 'vehicle_requisitions';

    protected $fillable = [
        'user_id', 'applicant_name', 'applicant_designation', 'purpose', 'official_details', 'destination',
        'distance_km', 'return_time', 'report_place', 'report_date', 'report_time', 'status',
        'vehicle_id', 'driver_id', 'fuel_amount', 'fuel_unit', 'decision_note', 'decided_by', 'decided_at',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = ['report_date' => 'date', 'decided_at' => 'datetime', 'distance_km' => 'decimal:2', 'fuel_amount' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class)->withTrashed();
    }

    public function driver()
    {
        return $this->belongsTo(VehicleDriver::class, 'driver_id')->withTrashed();
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
