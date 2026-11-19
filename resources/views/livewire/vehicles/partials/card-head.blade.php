{{-- Card header with the list title and the "add" button. Expects $t; $canAdd (default true). --}}
<div class="card-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
        <h3 class="card-title mb-0 mr-2">{{ $t('table') }}</h3>
        @if ($canAdd ?? true)
            <button type="button" class="btn btn-sm btn-success crud-btn" wire:click="openCreate"
                title="{{ $t('add') }}" aria-label="{{ $t('add') }}">
                <i class="fas fa-plus" aria-hidden="true"></i>
                <span class="d-none d-sm-inline ml-1">{{ $t('add') }}</span>
            </button>
        @endif
    </div>
</div>
