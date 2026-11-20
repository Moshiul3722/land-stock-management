<?php

namespace App\Livewire\Requisitions;

use App\Models\Requisition;
use App\Services\MenuPermissionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

/**
 * The requisition details dialog alone, for pages other than the requisition list (e.g. a
 * product's issue history): a link sends "open-requisition" with the id and the dialog opens
 * over that page; closing it leaves the user there. The dialog, its data and who may see which
 * requisition are those of the requisition list (ManageRequisitions).
 */
class RequisitionViewer extends ManageRequisitions
{
    public function mount(): void
    {
        abort_unless(app(MenuPermissionService::class)->allows(Auth::user(), 'requisitions.index'), 403);
    }

    #[On('open-requisition')]
    public function openRequisition(int $id): void
    {
        $this->openView($id);
    }

    public function render()
    {
        return view('livewire.requisitions.requisition-viewer', [
            'viewItemsPerPage' => self::VIEW_ITEMS_PER_PAGE,
            'todayLabel' => $this->localDigits(now()->locale(app()->getLocale() === 'bn' ? 'bn' : 'en')->translatedFormat(app()->getLocale() === 'bn' ? 'l, j F Y' : 'l, d M Y')),
            'canEdit' => fn (Requisition $r) => false,
            'formatDate' => fn ($date, bool $withTime = false) => $this->formatDate($date, $withTime),
            'digits' => fn ($value) => $this->localDigits($value),
            'statusLabel' => fn (?string $status) => $this->statusLabel($status),
        ]);
    }
}
