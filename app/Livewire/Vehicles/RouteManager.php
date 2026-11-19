<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use App\Models\VehicleRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

/** Vehicle routes: list with modal add / edit / delete. */
class RouteManager extends VehicleCrud
{
    public string $name = '';

    public string $startPoint = '';

    public string $endPoint = '';

    public string $description = '';

    protected function permissionRoutes(): array
    {
        return ['vehicle-routes.index'];
    }

    protected function modelClass(): string
    {
        return VehicleRoute::class;
    }

    protected function textKey(): string
    {
        return 'routes';
    }

    #[Computed]
    public function rows()
    {
        return VehicleRoute::query()
            ->withCount('vehicles')
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', $this->like())->orWhere('start_point', 'like', $this->like())
                ->orWhere('end_point', 'like', $this->like())->orWhere('description', 'like', $this->like())))
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    protected function fillForm(Model $record): void
    {
        $this->name = (string) $record->name;
        $this->startPoint = (string) $record->start_point;
        $this->endPoint = (string) $record->end_point;
        $this->description = (string) $record->description;
    }

    protected function resetFields(): void
    {
        $this->reset('name', 'startPoint', 'endPoint', 'description');
    }

    protected function beforeValidate(): void
    {
        foreach (['name', 'startPoint', 'endPoint', 'description'] as $field) {
            $this->{$field} = trim($this->{$field});
        }
    }

    protected function formRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('vehicle_routes', 'name')->whereNull('deleted_at')->ignore($this->editingId)],
            'startPoint' => ['nullable', 'string', 'max:150'],
            'endPoint' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function formLabels(): array
    {
        return [
            'name' => __('vehicle.fields.route_name'), 'startPoint' => __('vehicle.fields.start_point'),
            'endPoint' => __('vehicle.fields.end_point'), 'description' => __('vehicle.fields.description'),
        ];
    }

    protected function formAttributes(): array
    {
        return [
            'name' => $this->name,
            'start_point' => $this->startPoint ?: null,
            'end_point' => $this->endPoint ?: null,
            'description' => $this->description ?: null,
        ];
    }

    protected function recordLabel(Model $record): string
    {
        return (string) $record->name;
    }

    protected function blockedDelete(Model $record): ?string
    {
        return Vehicle::where('route_id', $record->id)->exists() ? __('vehicle.in_use') : null;
    }

    public function render()
    {
        return view('livewire.vehicles.route-manager', $this->pageData() + ['rows' => $this->rows]);
    }
}
