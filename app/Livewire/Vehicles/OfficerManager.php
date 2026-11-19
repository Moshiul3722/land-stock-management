<?php

namespace App\Livewire\Vehicles;

use App\Models\Department;
use App\Models\VehicleOfficer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

/** Officers / offices the vehicles serve: list with modal add / edit / delete. */
class OfficerManager extends VehicleCrud
{
    public string $nameBn = '';

    public string $name = '';

    public string $designation = '';

    public string $departmentId = '';

    public string $mobile = '';

    protected function permissionRoutes(): array
    {
        return ['vehicle-officers.index'];
    }

    protected function modelClass(): string
    {
        return VehicleOfficer::class;
    }

    protected function textKey(): string
    {
        return 'officers';
    }

    #[Computed]
    public function rows()
    {
        return VehicleOfficer::query()
            ->with('department:id,name,name_bn')
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', $this->like())->orWhere('name_bn', 'like', $this->like())
                ->orWhere('designation', 'like', $this->like())->orWhere('mobile', 'like', $this->like())
                ->orWhereHas('department', fn ($d) => $d->where('name', 'like', $this->like())->orWhere('name_bn', 'like', $this->like()))))
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function departments(): array
    {
        $bn = app()->getLocale() === 'bn';

        return Department::query()->orderBy('name')->get(['id', 'name', 'name_bn'])
            ->mapWithKeys(fn ($d) => [$d->id => $bn && $d->name_bn ? $d->name_bn : $d->name])->all();
    }

    protected function fillForm(Model $record): void
    {
        $this->nameBn = (string) $record->name_bn;
        $this->name = (string) $record->name;
        $this->designation = (string) $record->designation;
        $this->departmentId = (string) ($record->department_id ?? '');
        $this->mobile = (string) $record->mobile;
    }

    protected function resetFields(): void
    {
        $this->reset('nameBn', 'name', 'designation', 'departmentId', 'mobile');
    }

    protected function beforeValidate(): void
    {
        foreach (['nameBn', 'name', 'designation', 'mobile'] as $field) {
            $this->{$field} = trim($this->{$field});
        }
        $this->mobile = MobileNumber::normalise($this->mobile);
    }

    protected function formRules(): array
    {
        return [
            'nameBn' => ['required', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'designation' => ['nullable', 'string', 'max:150'],
            'departmentId' => ['nullable', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'mobile' => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
        ];
    }

    protected function formLabels(): array
    {
        return [
            'nameBn' => __('vehicle.fields.name_bn'), 'name' => __('vehicle.fields.name_en'),
            'designation' => __('vehicle.fields.designation'), 'departmentId' => __('vehicle.fields.department'),
            'mobile' => __('vehicle.fields.mobile'),
        ];
    }

    protected function formAttributes(): array
    {
        return [
            'name_bn' => $this->nameBn,
            'name' => $this->name !== '' ? $this->name : $this->nameBn,
            'designation' => $this->designation ?: null,
            'department_id' => $this->departmentId !== '' ? (int) $this->departmentId : null,
            'mobile' => $this->mobile ?: null,
        ];
    }

    protected function recordLabel(Model $record): string
    {
        return (string) ($record->name_bn ?: $record->name);
    }

    protected function blockedDelete(Model $record): ?string
    {
        return DB::table('vehicle_officer')->join('vehicles', 'vehicles.id', '=', 'vehicle_officer.vehicle_id')
            ->whereNull('vehicles.deleted_at')->where('vehicle_officer.officer_id', $record->id)->exists()
            ? __('vehicle.in_use') : null;
    }

    public function render()
    {
        return view('livewire.vehicles.officer-manager', $this->pageData() + ['rows' => $this->rows]);
    }
}
