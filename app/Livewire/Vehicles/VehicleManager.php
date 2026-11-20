<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use App\Models\VehicleDemand;
use App\Models\VehicleDriver;
use App\Models\VehicleOfficer;
use App\Models\VehicleRoute;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

/**
 * Vehicle details: number, type, driver, driving date, route and the offices / officers it
 * serves (more than one). List with modal add / edit / view / delete.
 */
class VehicleManager extends VehicleCrud
{
    public string $vehicleNo = '';

    public string $typeId = '';

    public string $driverId = '';

    public string $drivingDate = '';

    public string $routeId = '';

    /** @var array<int, string> */
    public array $officerIds = [];

    public bool $showView = false;

    #[Locked]
    public ?int $viewId = null;

    protected function permissionRoutes(): array
    {
        return ['vehicles.index'];
    }

    protected function modelClass(): string
    {
        return Vehicle::class;
    }

    protected function textKey(): string
    {
        return 'vehicles';
    }

    #[Computed]
    public function rows()
    {
        $like = $this->like();

        return Vehicle::query()
            ->with(['type', 'driver', 'route', 'officers.department:id,name,name_bn'])
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('vehicle_no', 'like', $like)
                ->orWhereHas('type', fn ($r) => $r->where('name', 'like', $like)->orWhere('name_bn', 'like', $like))
                ->orWhereHas('driver', fn ($r) => $r->where('name', 'like', $like)->orWhere('name_bn', 'like', $like)->orWhere('mobile', 'like', $like))
                ->orWhereHas('route', fn ($r) => $r->where('name', 'like', $like))
                ->orWhereHas('officers', fn ($r) => $r->where('name', 'like', $like)->orWhere('name_bn', 'like', $like)->orWhere('designation', 'like', $like))))
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function viewing(): ?Vehicle
    {
        return $this->viewId ? Vehicle::with(['type', 'driver', 'route', 'officers.department'])->find($this->viewId) : null;
    }

    /** Choices for the form */
    #[Computed]
    public function choices(): array
    {
        $bn = app()->getLocale() === 'bn';

        return [
            'types' => VehicleType::orderBy('name')->get()->mapWithKeys(fn ($t) => [$t->id => $t->label])->all(),
            'drivers' => VehicleDriver::orderBy('name')->get()
                ->mapWithKeys(fn ($d) => [$d->id => $d->label . ($d->mobile ? ' · ' . $this->localDigits($d->mobile) : '')])->all(),
            // A driver drives one vehicle only: drivers of other vehicles are listed but cannot be chosen
            'busyDrivers' => Vehicle::query()->whereNotNull('driver_id')
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->pluck('vehicle_no', 'driver_id')
                ->map(fn ($no) => __('vehicle.vehicles.driver_busy', ['vehicle' => $no]))->all(),
            'routes' => VehicleRoute::orderBy('name')->pluck('name', 'id')->all(),
            'officers' => VehicleOfficer::with('department:id,name,name_bn')->orderBy('name_bn')->get()
                ->map(fn ($o) => [
                    'id' => (string) $o->id,
                    'name' => $o->label,
                    'meta' => collect([$o->designation, $o->department ? ($bn && $o->department->name_bn ? $o->department->name_bn : $o->department->name) : null])->filter()->join(' · '),
                ])->all(),
        ];
    }

    public function openView(int $id): void
    {
        $this->authorizeAccess();
        if (! Vehicle::whereKey($id)->exists()) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }
        $this->viewId = $id;
        unset($this->viewing);
        $this->showView = true;
    }

    protected function fillForm(Model $record): void
    {
        $this->vehicleNo = (string) $record->vehicle_no;
        $this->typeId = (string) ($record->vehicle_type_id ?? '');
        $this->driverId = (string) ($record->driver_id ?? '');
        $this->drivingDate = $record->driving_date ? $record->driving_date->format('Y-m-d') : '';
        $this->routeId = (string) ($record->route_id ?? '');
        $this->officerIds = $record->officers()->pluck('vehicle_officers.id')->map(fn ($id) => (string) $id)->all();
    }

    protected function resetFields(): void
    {
        $this->reset('vehicleNo', 'typeId', 'driverId', 'drivingDate', 'routeId', 'officerIds');
        unset($this->choices);
    }

    protected function beforeValidate(): void
    {
        $this->vehicleNo = preg_replace('/\s+/u', ' ', trim($this->vehicleNo));
        $this->officerIds = array_values(array_unique(array_filter(array_map('strval', $this->officerIds))));
    }

    protected function formRules(): array
    {
        return [
            'vehicleNo' => ['required', 'string', 'max:50', Rule::unique('vehicles', 'vehicle_no')->whereNull('deleted_at')->ignore($this->editingId)],
            'typeId' => ['required', Rule::exists('vehicle_types', 'id')->whereNull('deleted_at')],
            'driverId' => ['nullable', Rule::exists('vehicle_drivers', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    $taken = $value !== '' && $value !== null && Vehicle::where('driver_id', (int) $value)
                        ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))->exists();
                    if ($taken) {
                        $fail(__('vehicle.vehicles.driver_taken'));
                    }
                }],
            'drivingDate' => ['nullable', 'date_format:Y-m-d'],
            'routeId' => ['nullable', Rule::exists('vehicle_routes', 'id')->whereNull('deleted_at')],
            'officerIds' => ['array'],
            'officerIds.*' => [Rule::exists('vehicle_officers', 'id')->whereNull('deleted_at')],
        ];
    }

    protected function formLabels(): array
    {
        return [
            'vehicleNo' => __('vehicle.fields.vehicle_no'), 'typeId' => __('vehicle.fields.vehicle_type'),
            'driverId' => __('vehicle.fields.driver'), 'drivingDate' => __('vehicle.fields.driving_date'),
            'routeId' => __('vehicle.fields.route'), 'officerIds' => __('vehicle.fields.officers'),
            'officerIds.*' => __('vehicle.fields.officers'),
        ];
    }

    protected function formAttributes(): array
    {
        return [
            'vehicle_no' => $this->vehicleNo,
            'vehicle_type_id' => (int) $this->typeId,
            'driver_id' => $this->driverId !== '' ? (int) $this->driverId : null,
            'driving_date' => $this->drivingDate !== '' ? $this->drivingDate : null,
            'route_id' => $this->routeId !== '' ? (int) $this->routeId : null,
        ];
    }

    protected function afterSave(Model $record, bool $creating): void
    {
        $record->officers()->sync(array_map('intval', $this->officerIds));
    }

    protected function recordLabel(Model $record): string
    {
        return (string) $record->vehicle_no;
    }

    protected function blockedDelete(Model $record): ?string
    {
        return VehicleDemand::where('vehicle_id', $record->id)->exists() ? __('vehicle.vehicles.in_use') : null;
    }

    protected function beforeDelete(Model $record): void
    {
        $record->officers()->detach();
    }

    public function render()
    {
        return view('livewire.vehicles.vehicle-manager', $this->pageData() + ['rows' => $this->rows]);
    }
}
