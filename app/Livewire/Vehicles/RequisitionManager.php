<?php

namespace App\Livewire\Vehicles;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDriver;
use App\Models\VehicleRequisition;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Throwable;

/**
 * Vehicle demand (the paper "vehicle requisition form"): any signed-in user asks for a vehicle;
 * Super Admin / Admin approve it with a vehicle, a driver and the fuel amount, or reject it.
 * A user sees and changes only their own requisitions (while still pending); Super Admin and
 * Admin see all of them. Each one can be printed like the paper form.
 */
class RequisitionManager extends VehicleCrud
{
    /** Roles that decide on requisitions and see all of them */
    private const DECIDERS = [1, 2];

    public string $purpose = 'official';

    public string $officialDetails = '';

    public string $destination = '';

    public string $distanceKm = '';

    public string $returnTime = '';

    public string $reportPlace = '';

    public string $reportDate = '';

    public string $reportTime = '';

    /** Message under the distance field about the automatic distance */
    #[Locked]
    public string $distanceNote = '';

    #[Locked]
    public bool $distanceFailed = false;

    public bool $showView = false;

    #[Locked]
    public ?int $viewId = null;

    /** Decision dialog */
    public bool $showDecide = false;

    #[Locked]
    public ?int $decideId = null;

    public string $decVehicleId = '';

    public string $decDriverId = '';

    public string $decFuelAmount = '';

    public string $decFuelUnit = '';

    public string $decNote = '';

    /** How the fuel amount was worked out (shown under the field) */
    #[Locked]
    public string $decFuelNote = '';

    protected function permissionRoutes(): array
    {
        return ['vehicle-requisitions.index'];
    }

    protected function modelClass(): string
    {
        return VehicleRequisition::class;
    }

    protected function textKey(): string
    {
        return 'requisitions';
    }

    /** Super Admin or Admin: sees every requisition and decides on them. */
    #[Computed]
    public function isDecider(): bool
    {
        return in_array((int) Auth::user()?->role_id, self::DECIDERS, true);
    }

    /** Requisitions the user may see. */
    private function visibleQuery()
    {
        return VehicleRequisition::query()->when(! $this->isDecider, fn ($q) => $q->where('user_id', Auth::id()));
    }

    /** Only the applicant's own requisitions, while still pending, can be changed or deleted. */
    protected function editableQuery()
    {
        return VehicleRequisition::query()->where('user_id', Auth::id())->where('status', 'pending');
    }

    #[Computed]
    public function rows()
    {
        $like = $this->like();

        return $this->visibleQuery()
            ->with(['vehicle', 'driver'])
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('destination', 'like', $like)->orWhere('applicant_name', 'like', $like)
                ->orWhere('report_place', 'like', $like)->orWhere('official_details', 'like', $like)
                ->orWhereHas('vehicle', fn ($v) => $v->where('vehicle_no', 'like', $like))
                ->orWhereHas('driver', fn ($d) => $d->where('name', 'like', $like)->orWhere('name_bn', 'like', $like))))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function viewing(): ?VehicleRequisition
    {
        return $this->viewId ? $this->visibleQuery()->with(['vehicle.type', 'driver', 'decider:id,name,name_bn'])->find($this->viewId) : null;
    }

    #[Computed]
    public function deciding(): ?VehicleRequisition
    {
        return $this->decideId ? VehicleRequisition::find($this->decideId) : null;
    }

    /** Choices in the decision dialog */
    #[Computed]
    public function decisionChoices(): array
    {
        return [
            'vehicles' => Vehicle::with('type')->orderBy('vehicle_no')->get()
                ->mapWithKeys(fn ($v) => [$v->id => $v->vehicle_no . ($v->type ? ' · ' . $v->type->label : '')])->all(),
            'drivers' => VehicleDriver::orderBy('name')->get()
                ->mapWithKeys(fn ($d) => [$d->id => $d->label . ($d->mobile ? ' · ' . $this->localDigits($d->mobile) : '')])->all(),
            'units' => array_combine(VehicleRequisition::FUEL_UNITS, VehicleRequisition::FUEL_UNITS),
        ];
    }

    public function openCreate(): void
    {
        parent::openCreate();
        $this->reportDate = now()->format('Y-m-d');
    }

    /** A changed place: the distance is worked out again (when both places are given). */
    public function updatedDestination(): void
    {
        $this->autoDistance(false);
    }

    public function updatedReportPlace(): void
    {
        $this->autoDistance(false);
    }

    /** The "calculate distance" button */
    public function calculateDistance(): void
    {
        $this->autoDistance(true);
    }

    /**
     * Round-trip road distance (report place -> destination -> report place) from Google Maps
     * (with a key) or OpenStreetMap,
     * put in the distance field; it can still be changed by hand.
     */
    private function autoDistance(bool $asked): void
    {
        $from = trim($this->reportPlace);
        $to = trim($this->destination);
        if ($from === '' || $to === '') {
            $this->distanceNote = $asked ? __('vehicle.requisitions.distance_need_places') : '';
            $this->distanceFailed = $asked;

            return;
        }

        $key = 'route-distance:' . Auth::id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 30)) {
            $this->distanceNote = __('vehicle.requisitions.distance_failed');
            $this->distanceFailed = true;

            return;
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);

        $result = app(\App\Services\RouteDistanceService::class)->drivingDistance($from, $to);
        if ($result['status'] !== 'ok') {
            $this->distanceNote = __('vehicle.requisitions.distance_' . ($result['status'] === 'not_found' ? 'not_found' : 'failed'));
            $this->distanceFailed = true;

            return;
        }

        $oneWay = round($result['meters'] / 1000, 1);
        $roundTrip = round($result['meters'] * 2 / 1000, 1);
        $this->distanceKm = rtrim(rtrim(number_format($roundTrip, 1, '.', ''), '0'), '.');
        $this->resetValidation('distanceKm');
        $this->distanceNote = __('vehicle.requisitions.distance_auto', [
            'source' => $result['source'] === 'google' ? 'Google Maps' : 'OpenStreetMap',
            'one' => $this->localDigits(rtrim(rtrim(number_format($oneWay, 1, '.', ''), '0'), '.')),
            'km' => $this->localDigits($this->distanceKm),
        ]);
        if (! empty($result['from_name']) && ! empty($result['to_name'])) {
            $this->distanceNote .= ' ' . __('vehicle.requisitions.distance_places', ['from' => $result['from_name'], 'to' => $result['to_name']]);
        }
        $this->distanceFailed = false;
    }

    public function openView(int $id): void
    {
        $this->authorizeAccess();
        if (! $this->visibleQuery()->whereKey($id)->exists()) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }
        $this->viewId = $id;
        unset($this->viewing);
        $this->showView = true;
    }

    /** Super Admin / Admin: open the approve / reject dialog of a pending requisition. */
    public function openDecide(int $id): void
    {
        $this->authorizeDecider();
        $record = VehicleRequisition::find($id);
        if (! $record) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }
        if ($record->status !== 'pending') {
            $this->notify('error', __('vehicle.requisitions.not_pending'));

            return;
        }

        $this->resetValidation();
        $this->decideId = $record->id;
        $this->decVehicleId = (string) ($record->vehicle_id ?? '');
        $this->decDriverId = (string) ($record->driver_id ?? '');
        $this->decFuelAmount = $record->fuel_amount !== null ? rtrim(rtrim((string) $record->fuel_amount, '0'), '.') : '';
        $this->decFuelUnit = (string) ($record->fuel_unit ?: VehicleRequisition::FUEL_UNITS[0]);
        $this->decNote = '';
        $this->decFuelNote = '';
        unset($this->deciding, $this->decisionChoices);
        if ($this->decFuelAmount === '') {
            $this->autoFuel();
        }
        $this->showView = false;
        $this->showDecide = true;
    }

    /** Choosing a vehicle fills in its driver (it can still be changed). */
    public function updatedDecVehicleId(): void
    {
        $driverId = $this->decVehicleId !== '' ? Vehicle::whereKey((int) $this->decVehicleId)->value('driver_id') : null;
        if ($driverId) {
            $this->decDriverId = (string) $driverId;
        }
        $this->autoFuel();
    }

    /**
     * Fuel for the chosen vehicle: the round-trip distance divided by the km the vehicle's
     * type goes on a litre, rounded up to the next whole litre. It can still be changed.
     */
    private function autoFuel(): void
    {
        $this->decFuelNote = '';
        $request = $this->deciding;
        $vehicle = $this->decVehicleId !== '' ? Vehicle::with('type')->find((int) $this->decVehicleId) : null;
        if (! $request || ! $vehicle) {
            return;
        }
        $distance = $request->distance_km !== null ? (float) $request->distance_km : 0.0;
        if ($distance <= 0) {
            $this->decFuelNote = __('vehicle.requisitions.fuel_no_distance');

            return;
        }
        $economy = $vehicle->type?->fuelEconomy();
        if (! $economy) {
            $this->decFuelNote = __('vehicle.requisitions.fuel_no_economy');

            return;
        }

        $litres = (int) ceil(round($distance / $economy['value'], 2));
        $this->decFuelAmount = (string) max(1, $litres);
        $this->decFuelUnit = 'লিটার';
        $this->resetValidation(['decFuelAmount', 'decFuelUnit']);
        $number = fn ($value) => $this->localDigits(rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'));
        $this->decFuelNote = __('vehicle.requisitions.fuel_auto', [
            'km' => $number($distance),
            'per' => $number($economy['value']),
            'exact' => $number($distance / $economy['value']),
            'litres' => $this->localDigits($this->decFuelAmount),
        ]) . ($economy['usual'] ? ' ' . __('vehicle.requisitions.fuel_usual') : '');
    }

    public function approve(): void
    {
        $this->authorizeDecider();
        $this->decFuelAmount = $this->bnToEn(trim($this->decFuelAmount));
        $this->decNote = trim($this->decNote);
        $choices = $this->decisionChoices;
        $this->validate([
            'decVehicleId' => ['required', Rule::in(array_map('strval', array_keys($choices['vehicles'])))],
            'decDriverId' => ['required', Rule::in(array_map('strval', array_keys($choices['drivers'])))],
            'decFuelAmount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'decFuelUnit' => ['required_with:decFuelAmount', 'nullable', Rule::in(VehicleRequisition::FUEL_UNITS)],
            'decNote' => ['nullable', 'string', 'max:1000'],
        ], [], $this->decisionLabels());

        $this->decide('approved', [
            'vehicle_id' => (int) $this->decVehicleId,
            'driver_id' => (int) $this->decDriverId,
            'fuel_amount' => $this->decFuelAmount !== '' ? $this->decFuelAmount : null,
            'fuel_unit' => $this->decFuelAmount !== '' ? $this->decFuelUnit : null,
        ]);
    }

    public function reject(): void
    {
        $this->authorizeDecider();
        $this->decNote = trim($this->decNote);
        $this->resetValidation();
        $this->validate(['decNote' => ['required', 'string', 'max:1000']],
            ['decNote.required' => __('vehicle.requisitions.reject_reason_required')], $this->decisionLabels());

        $this->decide('rejected', ['vehicle_id' => null, 'driver_id' => null, 'fuel_amount' => null, 'fuel_unit' => null]);
    }

    /** Save the decision (once only) and tell the applicant. */
    private function decide(string $status, array $attributes): void
    {
        try {
            $record = DB::transaction(function () use ($status, $attributes) {
                $record = VehicleRequisition::whereKey($this->decideId)->lockForUpdate()->first();
                if (! $record || $record->status !== 'pending') {
                    return null;
                }
                $record->update($attributes + [
                    'status' => $status,
                    'decision_note' => $this->decNote !== '' ? $this->decNote : null,
                    'decided_by' => Auth::id(),
                    'decided_at' => now(),
                    'updated_by' => Auth::id(),
                ]);

                return $record;
            });
        } catch (Throwable $e) {
            Log::error('Vehicle requisition decision failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        $this->showDecide = false;
        if (! $record) {
            $this->notify('error', __('vehicle.requisitions.not_pending'));

            return;
        }

        $text = $status === 'approved'
            ? __('vehicle.requisitions.notify_approved', ['id' => $record->id, 'vehicle' => $record->vehicle?->vehicle_no ?? ''])
            : __('vehicle.requisitions.notify_rejected', ['id' => $record->id]);
        app(NotificationService::class)->createNotification($record->user_id, $text, route('vehicle-requisitions.index'), Auth::id());

        $this->decideId = null;
        $this->notify('success', __('vehicle.requisitions.' . $status));
    }

    private function authorizeDecider(): void
    {
        $this->authorizeAccess();
        abort_unless($this->isDecider, 403);
    }

    private function decisionLabels(): array
    {
        return [
            'decVehicleId' => __('vehicle.requisitions.vehicle_no'), 'decDriverId' => __('vehicle.requisitions.driver_name'),
            'decFuelAmount' => __('vehicle.requisitions.fuel_amount'), 'decFuelUnit' => __('vehicle.requisitions.fuel_unit'),
            'decNote' => __('vehicle.requisitions.decision_note'),
        ];
    }

    private function bnToEn(string $value): string
    {
        return strtr($value, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
    }

    protected function fillForm(Model $record): void
    {
        $this->purpose = (string) $record->purpose;
        $this->officialDetails = (string) $record->official_details;
        $this->destination = (string) $record->destination;
        $this->distanceKm = $record->distance_km !== null ? rtrim(rtrim((string) $record->distance_km, '0'), '.') : '';
        $this->returnTime = $record->return_time ? substr((string) $record->return_time, 0, 5) : '';
        $this->reportPlace = (string) $record->report_place;
        $this->reportDate = $record->report_date ? $record->report_date->format('Y-m-d') : '';
        $this->reportTime = $record->report_time ? substr((string) $record->report_time, 0, 5) : '';
    }

    protected function resetFields(): void
    {
        $this->reset('purpose', 'officialDetails', 'destination', 'distanceKm', 'returnTime', 'reportPlace', 'reportDate', 'reportTime', 'distanceNote', 'distanceFailed');
    }

    protected function beforeValidate(): void
    {
        foreach (['officialDetails', 'destination', 'reportPlace'] as $field) {
            $this->{$field} = trim($this->{$field});
        }
        $this->distanceKm = $this->bnToEn(trim($this->distanceKm));
    }

    protected function formRules(): array
    {
        return [
            'purpose' => ['required', Rule::in(VehicleRequisition::PURPOSES)],
            'officialDetails' => ['required_if:purpose,official', 'nullable', 'string', 'max:1000'],
            'destination' => ['required', 'string', 'max:255'],
            'distanceKm' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'returnTime' => ['nullable', 'date_format:H:i'],
            'reportPlace' => ['required', 'string', 'max:255'],
            'reportDate' => ['required', 'date_format:Y-m-d'],
            'reportTime' => ['required', 'date_format:H:i'],
        ];
    }

    protected function formLabels(): array
    {
        $t = fn ($key) => __('vehicle.requisitions.' . $key);

        return [
            'purpose' => $t('purpose'), 'officialDetails' => $t('official_details'), 'destination' => $t('destination'),
            'distanceKm' => $t('distance'), 'returnTime' => $t('return_time'), 'reportPlace' => $t('report_place'),
            'reportDate' => $t('report_place') . ' — ' . $t('report_date'), 'reportTime' => $t('report_place') . ' — ' . $t('report_time'),
        ];
    }

    protected function formAttributes(): array
    {
        $attributes = [
            'purpose' => $this->purpose,
            'official_details' => $this->officialDetails !== '' ? $this->officialDetails : null,
            'destination' => $this->destination,
            'distance_km' => $this->distanceKm !== '' ? $this->distanceKm : null,
            'return_time' => $this->returnTime !== '' ? $this->returnTime : null,
            'report_place' => $this->reportPlace,
            'report_date' => $this->reportDate,
            'report_time' => $this->reportTime,
        ];

        // The applicant (name and designation as they are now) is set once, when applying
        if ($this->editingId === null) {
            $user = Auth::user();
            $attributes += [
                'user_id' => $user->id,
                'applicant_name' => (app()->getLocale() === 'bn' && $user->name_bn) ? $user->name_bn : ($user->name ?: $user->name_bn),
                'applicant_designation' => $user->designation,
                'status' => 'pending',
            ];
        }

        return $attributes;
    }

    /** A new requisition is sent to Super Admin and Admin. */
    protected function afterSave(Model $record, bool $creating): void
    {
        if (! $creating) {
            return;
        }
        $text = __('vehicle.requisitions.notify_new', ['id' => $record->id, 'name' => $record->applicant_name]);
        $url = route('vehicle-requisitions.index');
        User::query()->whereIn('role_id', self::DECIDERS)->where('is_active', 1)->whereNull('deleted_at')
            ->where('id', '!=', Auth::id())->pluck('id')
            ->each(fn ($id) => app(NotificationService::class)->createNotification($id, $text, $url, Auth::id()));
    }

    protected function recordLabel(Model $record): string
    {
        return (string) $record->destination;
    }

    public function render()
    {
        return view('livewire.vehicles.requisition-manager', $this->pageData() + ['rows' => $this->rows]);
    }
}
