<?php

namespace App\Livewire\Catalog;

use App\Livewire\Concerns\ManagesCrudTable;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * Product units: list with modal create / edit / delete.
 */
class UnitManager extends Component
{
    use ManagesCrudTable, WithPagination;

    public bool $showForm = false;

    #[Locked]
    public ?int $unitId = null;

    public string $name = '';

    /** Bangla name of the unit. */
    public string $nameBn = '';

    public bool $showDelete = false;

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteLabel = '';

    protected function permissionRoutes(): array
    {
        return ['product-units.index'];
    }

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    #[Computed]
    public function units()
    {
        $term = trim($this->search);

        $paginator = ProductUnit::query()
            ->when($term !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', '%' . $term . '%')
                ->orWhere('name_bn', 'like', '%' . $term . '%')))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        // How many products use each unit (products hold the unit name as text).
        $counts = Product::query()
            ->whereIn('product_unit', $paginator->pluck('name'))
            ->selectRaw('product_unit, count(*) as total')
            ->groupBy('product_unit')
            ->pluck('total', 'product_unit');

        $paginator->getCollection()->each(fn (ProductUnit $unit) => $unit->products_count = (int) ($counts[$unit->name] ?? 0));

        return $paginator;
    }

    public function openCreate(): void
    {
        $this->authorizeAccess();
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeAccess();
        $unit = ProductUnit::find($id);
        if (! $unit) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->resetForm();
        $this->unitId = $unit->id;
        $this->name = (string) $unit->name;
        $this->nameBn = (string) $unit->name_bn;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $this->name = trim($this->name);
        $this->nameBn = trim($this->nameBn);

        $this->validate([
            // products.product_unit allows 50 characters.
            'name' => ['required', 'string', 'max:50', Rule::unique('product_units', 'name')->whereNull('deleted_at')->ignore($this->unitId)],
            'nameBn' => ['required', 'string', 'max:50', Rule::unique('product_units', 'name_bn')->whereNull('deleted_at')->ignore($this->unitId)],
        ], [
            'name.required' => __('commontable.required_field', ['attribute' => __('unit.name_en')]),
            'name.unique' => __('unit.exists'),
            'nameBn.required' => __('commontable.required_field', ['attribute' => __('unit.name_bn')]),
            'nameBn.unique' => __('unit.exists'),
        ], [
            'name' => __('unit.name_en'),
            'nameBn' => __('unit.name_bn'),
        ]);

        $editing = $this->unitId !== null;

        try {
            DB::transaction(function () use ($editing) {
                if (! $editing) {
                    ProductUnit::create(['name' => $this->name, 'name_bn' => $this->nameBn, 'created_by' => Auth::id()]);

                    return;
                }

                $unit = ProductUnit::findOrFail($this->unitId);
                $oldName = $unit->name;
                $unit->update(['name' => $this->name, 'name_bn' => $this->nameBn, 'updated_by' => Auth::id()]);

                // Products refer to the unit by name, so a rename is carried over to them.
                if ($oldName !== $this->name) {
                    Product::where('product_unit', $oldName)->update(['product_unit' => $this->name]);
                }
            });
        } catch (Throwable $e) {
            Log::error('Product unit save failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('form', __('commontable.server_error'));
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        ProductUnit::flushLabels();
        $this->showForm = false;
        $this->resetForm();
        if (! $editing) {
            $this->resetPage();
        }
        unset($this->units);
        $this->notify('success', __($editing ? 'unit.updated' : 'unit.created'));
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeAccess();
        $unit = ProductUnit::find($id);
        if (! $unit) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->deleteId = $unit->id;
        $this->deleteLabel = $unit->name_bn ? $unit->name . ' (' . $unit->name_bn . ')' : (string) $unit->name;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $this->authorizeAccess();
        $this->showDelete = false;
        $unit = $this->deleteId ? ProductUnit::find($this->deleteId) : null;
        if (! $unit) {
            return;
        }

        if (Product::where('product_unit', $unit->name)->exists()) {
            $this->notify('error', __('unit.in_use'));

            return;
        }

        try {
            $unit->forceFill(['deleted_by' => Auth::id()])->save();
            $unit->delete();
        } catch (Throwable $e) {
            Log::error('Product unit delete failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('unit.delete_failed'));

            return;
        }

        $this->deleteId = null;
        unset($this->units);
        if ($this->units->isEmpty() && $this->getPage() > 1) {
            $this->previousPage();
            unset($this->units);
        }
        $this->notify('success', __('unit.deleted'));
    }

    public function render()
    {
        return view('livewire.catalog.unit-manager', $this->tableViewData() + ['rows' => $this->units]);
    }

    private function resetForm(): void
    {
        $this->reset('unitId', 'name', 'nameBn');
        $this->resetValidation();
    }
}
