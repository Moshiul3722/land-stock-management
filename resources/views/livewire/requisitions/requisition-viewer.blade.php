@php
    extract(require resource_path('views/livewire/requisitions/partials/ui-helpers.php'));
@endphp
{{-- Only the details dialog (see RequisitionViewer) --}}
<div class="tw-root" x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    <div class="tw-font-sans tw-text-slate-800">
        @include('livewire.requisitions.partials.view-modal')
    </div>
</div>
