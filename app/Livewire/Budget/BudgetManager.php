<?php

namespace App\Livewire\Budget;

use App\Livewire\Concerns\ManagesCrudTable;
use App\Models\Budget;
use App\Models\FiscalYear;
use App\Models\ProductCategory;
use App\Services\BudgetService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * Category budgets per fiscal year: list with fiscal-year filter and search; create, edit and
 * delete in modals. Saving goes through the existing BudgetService.
 *
 * A budget is identified by its category + fiscal year, so those two are fixed once it
 * exists; only the amount and the remarks can be edited.
 *
 * The purchase-type budget screen (PurchaseTypeBudgetManager) is the same screen for another
 * "owner": it overrides the small hooks at the bottom of this class. `categoryId` therefore
 * holds the id of the owner — a category here, a purchase type there.
 */
class BudgetManager extends Component
{
    use ManagesCrudTable, WithPagination;

    #[Url(as: 'year', except: '')]
    public string $yearFilter = '';

    public bool $showForm = false;

    #[Locked]
    public ?int $budgetId = null;

    public $categoryId = null;

    public $fiscalYearId = null;

    public string $amount = '';

    public string $remarks = '';

    /** Shown read-only while editing. */
    #[Locked]
    public string $categoryLabel = '';

    #[Locked]
    public string $fiscalYearLabel = '';

    #[Locked]
    public float $spentAmount = 0;

    public bool $showDelete = false;

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteLabel = '';

    protected function permissionRoutes(): array
    {
        return ['budgets.index', 'budgets.create'];
    }

    public function mount(): void
    {
        $this->authorizeAccess();

        // The sidebar "create" link and the old edit URL land here and open the modal.
        if (request()->boolean('create')) {
            $this->openCreate();
        } elseif (ctype_digit((string) request()->query('edit'))) {
            $this->openEdit((int) request()->query('edit'));
        }
    }

    public function updatedYearFilter(): void
    {
        $this->resetPage();
    }

    // ------------------------------------------------------------------
    // List
    // ------------------------------------------------------------------

    #[Computed]
    public function budgets()
    {
        $term = trim($this->search);
        $owner = $this->ownerRelation();

        return $this->query()
            ->with([$owner . ':id,name', 'fiscalYear:id,name'])
            ->when(ctype_digit($this->yearFilter), fn (Builder $query) => $query->where('fiscal_year_id', (int) $this->yearFilter))
            ->when($term !== '', function (Builder $query) use ($term, $owner) {
                $like = '%' . addcslashes($this->latinDigits($term), '%_\\') . '%';
                $query->where(function (Builder $inner) use ($like, $owner) {
                    $inner->where('remarks', 'like', $like)
                        ->orWhereHas($owner, fn (Builder $related) => $related->where('name', 'like', $like))
                        ->orWhereHas('fiscalYear', fn (Builder $year) => $year->where('name', 'like', $like));
                });
            })
            ->orderByDesc('fiscal_year_id')
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    /** Every fiscal year, for the filter above the table. */
    #[Computed]
    public function yearOptions()
    {
        return FiscalYear::query()->orderByDesc('name')->get(['id', 'name']);
    }

    /** Active fiscal years, for a new budget. */
    #[Computed]
    public function activeYearOptions()
    {
        return FiscalYear::query()->where('is_active', true)->orderByDesc('name')->get(['id', 'name']);
    }

    /** What a budget can be defined for: categories here, purchase types in the subclass. */
    #[Computed]
    public function ownerOptions()
    {
        return ProductCategory::query()->orderBy('name')->get(['id', 'name']);
    }

    // ------------------------------------------------------------------
    // Create / edit
    // ------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->authorizeAccess();
        $this->resetForm();
        $this->fiscalYearId = $this->currentFiscalYearId();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeAccess();
        $budget = $this->findWithNames($id);
        if (! $budget) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->resetForm();
        $this->budgetId = $budget->id;
        $this->categoryId = $budget->{$this->ownerKey()};
        $this->fiscalYearId = $budget->fiscal_year_id;
        $this->categoryLabel = $this->ownerName($budget);
        $this->fiscalYearLabel = (string) ($budget->fiscalYear->name ?? '—');
        $this->spentAmount = (float) $budget->spent_amount;
        $this->amount = $this->plainAmount($budget->amount);
        $this->remarks = (string) $budget->remarks;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $editing = $this->budgetId !== null;

        // Bangla digits and thousands separators are accepted; the value is stored as plain 0-9.
        $this->amount = str_replace([',', ' '], '', $this->latinDigits(trim($this->amount)));
        $this->remarks = trim($this->remarks);

        $rules = [
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
        if (! $editing) {
            $rules['categoryId'] = ['required', 'integer', Rule::in($this->ownerOptions->pluck('id')->all())];
            $rules['fiscalYearId'] = ['required', 'integer', Rule::exists('fiscal_years', 'id')->where('is_active', true)];
        }

        $data = $this->validate($rules, [
            'categoryId.required' => __('commontable.required_field', ['attribute' => $this->label('owner')]),
            'categoryId.in' => __('commontable.required_field', ['attribute' => $this->label('owner')]),
            'fiscalYearId.required' => __('commontable.required_field', ['attribute' => __('budget.fiscal_year')]),
            'amount.required' => __('commontable.required_field', ['attribute' => __('budget.manage.amount')]),
            'amount.numeric' => __('budget.manage.amount_invalid'),
            'amount.regex' => __('budget.manage.amount_invalid'),
            'amount.min' => __('budget.manage.amount_invalid'),
            'amount.max' => __('budget.manage.amount_too_large'),
        ], [
            'categoryId' => $this->label('owner'),
            'fiscalYearId' => __('budget.fiscal_year'),
            'amount' => __('budget.manage.amount'),
            'remarks' => __('budget.remarks'),
        ]);

        if ($editing) {
            $budget = $this->query()->find($this->budgetId);
            if (! $budget) {
                $this->showForm = false;
                $this->notify('error', __('commontable.not_found'));

                return;
            }
            // The allocation cannot drop below what has already been spent from it.
            if ((float) $data['amount'] < (float) $budget->spent_amount) {
                $this->addError('amount', __('budget.manage.below_spent', ['spent' => $this->money($budget->spent_amount)]));

                return;
            }
        } elseif ($this->query()->where($this->ownerKey(), $data['categoryId'])->where('fiscal_year_id', $data['fiscalYearId'])->exists()) {
            // One active budget per owner and fiscal year.
            $this->addError('categoryId', $this->label('exists'));

            return;
        }

        $payload = [
            'amount' => $data['amount'],
            'remarks' => $data['remarks'] !== '' ? $data['remarks'] : null,
        ];

        try {
            $service = $this->service();
            $editing
                ? $service->update($payload, $this->budgetId)
                : $service->store($payload + [$this->ownerKey() => (int) $data['categoryId'], 'fiscal_year_id' => (int) $data['fiscalYearId']]);
        } catch (Throwable $e) {
            Log::error('Budget save failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('form', __('budget.manage.save_failed'));
            $this->notify('error', __('budget.manage.save_failed'));

            return;
        }

        $this->showForm = false;
        $this->resetForm();
        if (! $editing) {
            $this->resetPage();
        }
        unset($this->budgets);
        $this->notify('success', __($editing ? 'budget.manage.updated' : 'budget.manage.created'));
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function confirmDelete(int $id): void
    {
        $this->authorizeAccess();
        $budget = $this->findWithNames($id);
        if (! $budget) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->deleteId = $budget->id;
        $this->deleteLabel = $this->ownerName($budget) . ' · ' . $this->localDigits($budget->fiscalYear->name ?? '—');
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $this->authorizeAccess();
        $this->showDelete = false;
        $budget = $this->deleteId ? $this->query()->find($this->deleteId) : null;
        if (! $budget) {
            return;
        }

        try {
            $budget->forceFill(['deleted_by' => Auth::id()])->save();
            $this->service()->destroy($budget->id);
        } catch (Throwable $e) {
            Log::error('Budget delete failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('budget.manage.delete_failed'));

            return;
        }

        $this->deleteId = null;
        unset($this->budgets);
        if ($this->budgets->isEmpty() && $this->getPage() > 1) {
            $this->previousPage();
            unset($this->budgets);
        }
        $this->notify('success', __('budget.manage.deleted'));
    }

    // ------------------------------------------------------------------

    public function render()
    {
        return view('livewire.budget.budget-manager', $this->tableViewData() + [
            'rows' => $this->budgets,
            'money' => fn ($value) => $this->money($value),
            'labels' => $this->labels(),
            'ownerName' => fn ($budget) => $this->ownerName($budget),
        ]);
    }

    // ------------------------------------------------------------------
    // Hooks: what differs between the category and the purchase-type budget screens
    // ------------------------------------------------------------------

    /** @return class-string<\Illuminate\Database\Eloquent\Model> */
    protected function modelClass(): string
    {
        return Budget::class;
    }

    /** Column holding the owner's id. */
    protected function ownerKey(): string
    {
        return 'category_id';
    }

    /** Relation giving the owner (it must have a `name`). */
    protected function ownerRelation(): string
    {
        return 'category';
    }

    /** Service with store(), update() and destroy(). */
    protected function service()
    {
        return app(BudgetService::class);
    }

    /** Texts that name the owner. */
    protected function labels(): array
    {
        return [
            'title' => __('budget.budget_management'),
            'list' => __('budget.budget_list'),
            'owner' => __('budget.manage.category'),
            'select_owner' => __('budget.manage.select_category'),
            'search_owner' => __('budget.manage.search_category'),
            'search_placeholder' => __('budget.manage.search_placeholder'),
            'hint' => __('budget.manage.hint'),
            'edit_hint' => __('budget.manage.edit_hint'),
            'exists' => __('budget.manage.exists'),
        ];
    }

    // ------------------------------------------------------------------

    protected function label(string $key): string
    {
        return $this->labels()[$key];
    }

    protected function query(): Builder
    {
        return $this->modelClass()::query();
    }

    protected function findWithNames(int $id)
    {
        return $this->query()->with([$this->ownerRelation() . ':id,name', 'fiscalYear:id,name'])->find($id);
    }

    protected function ownerName($budget): string
    {
        return (string) ($budget->{$this->ownerRelation()}->name ?? '—');
    }

    /** 1234567.5 → 12,34,567.50 style is not used here: plain thousands grouping, localized digits. */
    private function money($value): string
    {
        return $this->localDigits(number_format((float) $value, 2));
    }

    private function plainAmount($value): string
    {
        $text = number_format((float) $value, 2, '.', '');

        return str_ends_with($text, '.00') ? substr($text, 0, -3) : $text;
    }

    private function latinDigits(string $value): string
    {
        return strtr($value, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
    }

    private function currentFiscalYearId(): ?int
    {
        $today = now()->format('Y-m-d');

        return FiscalYear::query()
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->value('id');
    }

    private function resetForm(): void
    {
        $this->reset('budgetId', 'categoryId', 'fiscalYearId', 'amount', 'remarks', 'categoryLabel', 'fiscalYearLabel', 'spentAmount');
        $this->resetValidation();
    }
}
