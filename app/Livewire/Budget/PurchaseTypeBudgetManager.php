<?php

namespace App\Livewire\Budget;

use App\Models\ProductPurchaseType;
use App\Models\PurchaseTypeBudget;
use App\Services\PurchaseTypeBudgetService;
use Livewire\Attributes\Computed;

/**
 * Budgets per purchase type and fiscal year: the same screen as BudgetManager, for purchase
 * types instead of categories.
 */
class PurchaseTypeBudgetManager extends BudgetManager
{
    /**
     * Only these purchase types (RFQ, DPM) have a budget: lot approval checks the
     * purchase-type budget for exactly these ids (ApprovalService).
     */
    private const BUDGETED_TYPE_IDS = [1, 2];

    protected function permissionRoutes(): array
    {
        return ['ptbudgets.index', 'ptbudgets.create'];
    }

    #[Computed]
    public function ownerOptions()
    {
        return ProductPurchaseType::query()->whereIn('id', self::BUDGETED_TYPE_IDS)->orderBy('name')->get(['id', 'name']);
    }

    protected function modelClass(): string
    {
        return PurchaseTypeBudget::class;
    }

    protected function ownerKey(): string
    {
        return 'pt_id';
    }

    protected function ownerRelation(): string
    {
        return 'PurchaseType';
    }

    protected function service()
    {
        return app(PurchaseTypeBudgetService::class);
    }

    protected function labels(): array
    {
        return [
            'title' => __('budget.pt.title'),
            'list' => __('budget.pt.list'),
            'owner' => __('budget.pt.owner'),
            'select_owner' => __('budget.pt.select_owner'),
            'search_owner' => __('budget.pt.search_owner'),
            'search_placeholder' => __('budget.pt.search_placeholder'),
            'hint' => __('budget.pt.hint'),
            'edit_hint' => __('budget.pt.edit_hint'),
            'exists' => __('budget.pt.exists'),
        ];
    }
}
