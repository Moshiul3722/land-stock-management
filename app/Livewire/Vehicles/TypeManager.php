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

    public string $kmPerLitre = '';

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
        $this->kmPerLitre = $record->km_per_litre !== null ? rtrim(rtrim((string) $record->km_per_litre, '0'), '.') : '';
    }

    protected function resetFields(): void
    {
        $this->reset('nameBn', 'name', 'kmPerLitre');
    }

    protected function beforeValidate(): void
    {
        $this->nameBn = trim($this->nameBn);
        $this->name = trim($this->name);
        $this->kmPerLitre = strtr(trim($this->kmPerLitre), ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
    }

    protected function formRules(): array
    {
        $unique = fn ($column) => Rule::unique('vehicle_types', $column)->whereNull('deleted_at')->ignore($this->editingId);

        return [
            'nameBn' => ['required', 'string', 'max:100', $unique('name_bn')],
            'name' => ['required', 'string', 'max:100', $unique('name')],
            'kmPerLitre' => ['nullable', 'numeric', 'min:0.5', 'max:200'],
        ];
    }

    protected function formLabels(): array
    {
        return ['nameBn' => __('vehicle.fields.name_bn'), 'name' => __('vehicle.fields.name_en'), 'kmPerLitre' => __('vehicle.types.km_per_litre')];
    }

    protected function formAttributes(): array
    {
        return ['name_bn' => $this->nameBn, 'name' => $this->name, 'km_per_litre' => $this->kmPerLitre !== '' ? $this->kmPerLitre : null];
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
