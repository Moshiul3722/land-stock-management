<?php

namespace App\Http\Controllers;

use App\Models\VehicleRequisition;
use Illuminate\Support\Facades\Auth;

/**
 * The vehicle requisition printed like the paper form (A4). ?preview=1 shows it on an A4
 * sheet with a print button; otherwise the print dialog opens at once. Only the applicant,
 * Super Admin and Admin may open it.
 */
class VehicleRequisitionController extends Controller
{
    public function printForm(VehicleRequisition $vehicleRequisition)
    {
        $user = Auth::user();
        abort_unless(
            $user && ((int) $vehicleRequisition->user_id === (int) $user->id || in_array((int) $user->role_id, [1, 2], true)),
            403
        );

        $vehicleRequisition->load(['user', 'vehicle', 'driver', 'decider']);

        return view('vehicle-requisitions.print', ['req' => $vehicleRequisition]);
    }
}
