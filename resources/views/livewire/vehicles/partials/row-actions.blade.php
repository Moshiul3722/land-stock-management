{{-- Edit / delete buttons of a list row. Expects $id and $label; $view (optional) adds a view button. --}}
<td class="crud-actions">
    @if (! empty($view))
        <button type="button" class="btn btn-sm btn-info crud-btn" wire:click="openView({{ $id }})"
            data-tip="{{ __('vehicle.view') }}" aria-label="{{ __('vehicle.view') }}: {{ $label }}">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </button>
    @endif
    <button type="button" class="btn btn-sm btn-primary crud-btn" wire:click="openEdit({{ $id }})"
        data-tip="{{ __('buttons.edit') }}" aria-label="{{ __('buttons.edit') }}: {{ $label }}">
        <i class="fas fa-edit" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger crud-btn" wire:click="confirmDelete({{ $id }})"
        data-tip="{{ __('buttons.delete') }}" aria-label="{{ __('buttons.delete') }}: {{ $label }}">
        <i class="fas fa-trash-alt" aria-hidden="true"></i>
    </button>
</td>
