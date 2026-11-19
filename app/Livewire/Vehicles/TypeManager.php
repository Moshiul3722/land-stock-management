<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

/** Vehicle types: list with modal add / edit / delete. */
class TypeManager extends VehicleCrud
{
    public string $nameBn = '';

    public string $name = '';

    protected function permissionRoutes(): array
    {
        return ['vehicle-types.index'];
    }

    protected function modelClass(): string
    {
        return VehicleType::class;
    }

    protected function textKey(): string
    {
        return 'types';
    }

    #[Computed]
    public function rows()
    {
        return VehicleType::query()
            ->withCount('vehicles')
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', $this->like())->orWhere('name_bn', 'like', $this->like())))
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    protected function fillForm(Model $record): void
    {
        $this->nameBn = (string) $record->name_bn;
        $this->name = (string) $record->name;
    }

    protected function resetFields(): void
    {
        $this->reset('nameBn', 'name');
    }

    protected function beforeValidate(): void
    {
        $this->nameBn = trim($this->nameBn);
        $this->name = trim($this->name);
    }

    protected function formRules(): array
    {
        $unique = fn ($column) => Rule::unique('vehicle_types', $column)->whereNull('deleted_at')->ignore($this->editingId);

        return [
            'nameBn' => ['required', 'string', 'max:100', $unique('name_bn')],
            'name' => ['required', 'string', 'max:100', $unique('name')],
        ];
    }

    protected function formLabels(): array
    {
        return ['nameBn' => __('vehicle.fields.name_bn'), 'name' => __('vehicle.fields.name_en')];
    }

    protected function formAttributes(): array
    {
        return ['name_bn' => $this->nameBn, 'name' => $this->name];
    }

    protected function recordLabel(Model $record): string
    {
        return trim($record->name_bn . ' (' . $record->name . ')');
    }

    protected function blockedDelete(Model $record): ?string
    {
        return Vehicle::where('vehicle_type_id', $record->id)->exists() ? __('vehicle.in_use') : null;
    }

    public function render()
    {
        return view('livewire.vehicles.type-manager', $this->pageData() + ['rows' => $this->rows]);
    }
}
