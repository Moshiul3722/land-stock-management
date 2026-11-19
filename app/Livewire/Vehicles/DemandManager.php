<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use App\Models\VehicleDemand;
use App\Models\VehicleDriver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

/**
 * Vehicle demand form: a driver asks for fuel, repair or parts for a vehicle assigned to them.
 * A signed-in driver (role "Vehicle Driver") sees and edits only their own demands, for their
 * own vehicles; other roles allowed on the page see every demand.
 */
class DemandManager extends VehicleCrud
{
    public string $vehicleId = '';

    public string $demandType = '';

    public string $quantity = '';

    public string $unit = '';

    public string $reason = '';

    public string $demandDate = '';

    protected function permissionRoutes(): array
    {
        return ['vehicle-demands.index'];
    }

    protected function modelClass(): string
    {
        return VehicleDemand::class;
    }

    protected function textKey(): string
    {
        return 'demands';
    }

    /** The signed-in user is a vehicle driver (sees only their own demands). */
    #[Computed]
    public function isDriver(): bool
    {
        return (int) Auth::user()?->role_id === VehicleDriver::roleId();
    }

    /** The driver record of the signed-in driver (null for other roles or an unlinked account). */
    #[Computed]
    public function me(): ?VehicleDriver
    {
        return $this->isDriver ? VehicleDriver::where('user_id', Auth::id())->first() : null;
    }

    protected function editableQuery()
    {
        return VehicleDemand::query()
            ->when($this->isDriver, fn ($q) => $q->where('driver_id', $this->me?->id ?? 0));
    }

    /** Vehicles that can be chosen: the driver's own, or every vehicle for other roles. */
    #[Computed]
    public function vehicleChoices(): array
    {
        return Vehicle::query()
            ->with('type')
            ->when($this->isDriver, fn ($q) => $q->where('driver_id', $this->me?->id ?? 0))
            ->orderBy('vehicle_no')
            ->get()
            ->mapWithKeys(fn ($v) => [$v->id => $v->vehicle_no . ($v->type ? ' · ' . $v->type->label : '')])
            ->all();
    }

    #[Computed]
    public function rows()
    {
        $like = $this->like();
        $typeMatches = collect(VehicleDemand::TYPES)
            ->filter(fn ($type) => trim($this->search) !== '' && str_contains(mb_strtolower(__('vehicle.demands.types.' . $type)), mb_strtolower(trim($this->search))))
            ->values()->all();

        return $this->editableQuery()
            ->with(['vehicle.type', 'driver'])
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('reason', 'like', $like)->orWhere('unit', 'like', $like)
                ->orWhereIn('demand_type', $typeMatches)
                ->orWhereHas('vehicle', fn ($v) => $v->where('vehicle_no', 'like', $like))
                ->orWhereHas('driver', fn ($d) => $d->where('name', 'like', $like)->orWhere('name_bn', 'like', $like))))
            ->orderByDesc('demand_date')
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    /** A driver can add only when they have a vehicle; other roles when any vehicle exists. */
    #[Computed]
    public function canAdd(): bool
    {
        return count($this->vehicleChoices) > 0;
    }

    public function openCreate(): void
    {
        if (! $this->canAdd) {
            $this->notify('error', $this->isDriver && ! $this->me ? __('vehicle.demands.not_driver') : __('vehicle.demands.no_vehicle'));

            return;
        }
        parent::openCreate();
        $this->demandDate = now()->format('Y-m-d');
        if (count($this->vehicleChoices) === 1) {
            $this->vehicleId = (string) array_key_first($this->vehicleChoices);
        }
    }

    protected function fillForm(Model $record): void
    {
        $this->vehicleId = (string) $record->vehicle_id;
        $this->demandType = (string) $record->demand_type;
        $this->quantity = $record->quantity !== null ? rtrim(rtrim((string) $record->quantity, '0'), '.') : '';
        $this->unit = (string) $record->unit;
        $this->reason = (string) $record->reason;
        $this->demandDate = $record->demand_date ? $record->demand_date->format('Y-m-d') : '';
    }

    protected function resetFields(): void
    {
        $this->reset('vehicleId', 'demandType', 'quantity', 'unit', 'reason', 'demandDate');
    }

    protected function beforeValidate(): void
    {
        $this->quantity = strtr(trim($this->quantity), ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $this->unit = trim($this->unit);
        $this->reason = trim($this->reason);
    }

    protected function formRules(): array
    {
        return [
            'vehicleId' => ['required', Rule::in(array_map('strval', array_keys($this->vehicleChoices)))],
            'demandType' => ['required', Rule::in(VehicleDemand::TYPES)],
            'quantity' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'unit' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'string', 'max:1000'],
            'demandDate' => ['required', 'date_format:Y-m-d'],
        ];
    }

    protected function formLabels(): array
    {
        return [
            'vehicleId' => __('vehicle.fields.vehicle'), 'demandType' => __('vehicle.fields.demand_type'),
            'quantity' => __('vehicle.fields.quantity'), 'unit' => __('vehicle.fields.unit'),
            'reason' => __('vehicle.fields.reason'), 'demandDate' => __('vehicle.fields.demand_date'),
        ];
    }

    protected function formAttributes(): array
    {
        // The demand is the driver's: the signed-in driver, or the vehicle's driver otherwise
        $driverId = $this->isDriver
            ? $this->me?->id
            : Vehicle::whereKey((int) $this->vehicleId)->value('driver_id');

        return [
            'vehicle_id' => (int) $this->vehicleId,
            'driver_id' => $driverId,
            'demand_type' => $this->demandType,
            'quantity' => $this->quantity !== '' ? $this->quantity : null,
            'unit' => $this->unit ?: null,
            'reason' => $this->reason,
            'demand_date' => $this->demandDate,
        ];
    }

    protected function recordLabel(Model $record): string
    {
        return trim(($record->vehicle?->vehicle_no ?? '') . ' · ' . __('vehicle.demands.types.' . $record->demand_type), ' ·');
    }

    public function render()
    {
        return view('livewire.vehicles.demand-manager', $this->pageData() + ['rows' => $this->rows]);
    }
}
