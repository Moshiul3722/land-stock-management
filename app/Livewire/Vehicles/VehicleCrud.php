<?php

namespace App\Livewire\Vehicles;

use App\Livewire\Concerns\ManagesCrudTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * Shared behaviour of the vehicle management list pages: search, page size, the add / edit
 * modal and the delete confirmation, all checked against the page's menu permission.
 *
 * A page defines its model, its permission route, its list query, the form fields (fill,
 * reset, validation rules, saved attributes) and its view; see the classes in this folder.
 */
abstract class VehicleCrud extends Component
{
    use ManagesCrudTable, WithPagination;

    public bool $showForm = false;

    #[Locked]
    public ?int $editingId = null;

    public bool $showDelete = false;

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteLabel = '';

    /** @return class-string<Model> */
    abstract protected function modelClass(): string;

    /** Key in lang/vehicle.php for the page's texts, e.g. "types". */
    abstract protected function textKey(): string;

    abstract protected function fillForm(Model $record): void;

    abstract protected function resetFields(): void;

    /** @return array<string, mixed> */
    abstract protected function formRules(): array;

    /** @return array<string, mixed> attributes saved on the record */
    abstract protected function formAttributes(): array;

    abstract protected function recordLabel(Model $record): string;

    /** Message when the record may not be deleted (still used elsewhere), else null. */
    protected function blockedDelete(Model $record): ?string
    {
        return null;
    }

    /** Extra work after a save, inside the same transaction (e.g. related rows). */
    protected function afterSave(Model $record, bool $creating): void
    {
    }

    /** Extra work before a delete, inside the same transaction. */
    protected function beforeDelete(Model $record): void
    {
    }

    /** Field names => their label, for validation messages. */
    protected function formLabels(): array
    {
        return [];
    }

    /** Records the signed-in user may edit / delete (all, unless a page narrows it). */
    protected function editableQuery()
    {
        return ($this->modelClass())::query();
    }

    public function mount(): void
    {
        $this->authorizeAccess();
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
        $record = $this->editableQuery()->find($id);
        if (! $record) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->resetForm();
        $this->editingId = $record->id;
        $this->fillForm($record);
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $this->beforeValidate();
        $this->validate($this->formRules(), [], $this->formLabels());

        $creating = $this->editingId === null;

        try {
            DB::transaction(function () use ($creating) {
                $attributes = $this->formAttributes();
                if ($creating) {
                    $record = ($this->modelClass())::create($attributes + ['created_by' => Auth::id()]);
                } else {
                    $record = $this->editableQuery()->findOrFail($this->editingId);
                    $record->update($attributes + ['updated_by' => Auth::id()]);
                }
                $this->afterSave($record, $creating);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Vehicle management save failed', ['page' => static::class, 'user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('form', __('commontable.server_error'));
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        $this->showForm = false;
        $this->resetForm();
        if ($creating) {
            $this->resetPage();
        }
        $this->notify('success', __($creating ? 'vehicle.saved' : 'vehicle.updated'));
    }

    /** Tidy the inputs before they are checked (trim, digits ...). */
    protected function beforeValidate(): void
    {
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeAccess();
        $record = $this->editableQuery()->find($id);
        if (! $record) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->deleteId = $record->id;
        $this->deleteLabel = $this->recordLabel($record);
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $this->authorizeAccess();
        $this->showDelete = false;
        $record = $this->deleteId ? $this->editableQuery()->find($this->deleteId) : null;
        if (! $record) {
            return;
        }

        if ($message = $this->blockedDelete($record)) {
            $this->notify('error', $message);

            return;
        }

        try {
            DB::transaction(function () use ($record) {
                $this->beforeDelete($record);
                $record->forceFill(['deleted_by' => Auth::id()])->save();
                $record->delete();
            });
        } catch (Throwable $e) {
            Log::error('Vehicle management delete failed', ['page' => static::class, 'user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        $this->deleteId = null;
        $this->notify('success', __('vehicle.deleted'));
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->resetFields();
        $this->resetValidation();
    }

    /** Variables every vehicle list view needs. */
    protected function pageData(): array
    {
        $key = $this->textKey();

        return $this->tableViewData() + [
            't' => fn (string $name) => __("vehicle.{$key}.{$name}"),
            'f' => fn (string $name) => __("vehicle.fields.{$name}"),
            'searching' => trim($this->search) !== '',
        ];
    }

    /** Search helper: the term with LIKE wildcards around it. */
    protected function like(): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($this->search)) . '%';
    }
}
