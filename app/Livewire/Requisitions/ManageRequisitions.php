<?php

namespace App\Livewire\Requisitions;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Requisition;
use App\Services\MenuPermissionService;
use App\Services\RequisitionService;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * "রিকুইজিশন ব্যবস্থাপনা" — list, search, filter and modal CRUD for the signed-in
 * user's own requisitions.
 *
 * Business rules come from RequisitionService (unchanged): only the requester's own
 * requisitions are listed, only drafts can be edited or deleted, and "submit" starts
 * the approval workflow. This component adds per-action authorization, because
 * Livewire requests bypass the `permission` route middleware.
 */
class ManageRequisitions extends Component
{
    use WithPagination;

    /** Statuses a requisition can have (same list the old index page offered). */
    public const STATUSES = ['draft', 'pending', 'approved', 'rejected', 'processed'];

    private const PER_PAGE_OPTIONS = [10, 25, 50];

    // ---- List state (kept in the URL so refresh / back keep the filters) ----

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    #[Url(as: 'per', except: 10)]
    public int $perPage = 10;

    // ---- Modal state ----

    public bool $showForm = false;

    public bool $showView = false;

    public bool $showDelete = false;

    /** ID of the requisition being edited; null while creating. */
    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public ?int $viewingId = null;

    #[Locked]
    public ?int $deletingId = null;

    #[Locked]
    public int $deletingItemsCount = 0;

    // ---- Create / edit form ----

    public ?int $categoryId = null;

    public ?int $productId = null;

    public $quantity = 1;

    /** @var array<int, array{product_id: int, quantity: int|string}> */
    public array $items = [];

    public string $remarks = '';

    public function mount(): void
    {
        abort_unless($this->can('requisitions.index'), 403);

        if (! in_array($this->perPage, self::PER_PAGE_OPTIONS, true)) {
            $this->perPage = 10;
        }

        // The sidebar's "Create Requisition" link (route requisitions.create) lands here
        // with ?create=1 and opens the create modal straight away.
        if (request()->boolean('create') && $this->can('requisitions.create')) {
            $this->openCreate();
        }
    }

    // ------------------------------------------------------------------
    // List
    // ------------------------------------------------------------------

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'dateFrom', 'dateTo', 'perPage'], true)) {
            if ($property === 'perPage' && ! in_array((int) $this->perPage, self::PER_PAGE_OPTIONS, true)) {
                $this->perPage = 10;
            }
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'status', 'dateFrom', 'dateTo');
        $this->resetPage();
    }

    #[Computed]
    public function requisitions()
    {
        $status = in_array($this->status, self::STATUSES, true) ? $this->status : null;
        $from = $this->parseDate($this->dateFrom);
        $to = $this->parseDate($this->dateTo);
        $term = trim($this->search);

        return $this->ownRequisitions()
            ->withCount('items')
            ->with(['items.product:id,name'])
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($from, fn (Builder $q) => $q->whereDate('requisition_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('requisition_date', '<=', $to))
            ->when($term !== '', function (Builder $q) use ($term) {
                $id = ltrim($this->toLatinDigits($term), '#');
                $q->where(function (Builder $inner) use ($term, $id) {
                    if (ctype_digit($id)) {
                        $inner->orWhere('id', (int) $id);
                    }
                    $inner->orWhereHas('items.product', fn (Builder $p) => $p->where('name', 'like', '%' . $term . '%'));
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function hasAnyRequisition(): bool
    {
        return $this->ownRequisitions()->exists();
    }

    protected function hasFilters(): bool
    {
        return $this->search !== '' || $this->status !== '' || $this->dateFrom !== '' || $this->dateTo !== '';
    }

    // ------------------------------------------------------------------
    // Create / edit
    // ------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->authorizeOrFail($this->can('requisitions.create'));

        $this->resetForm();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $requisition = $this->findEditable($id);
        if (! $requisition) {
            return;
        }

        $this->showView = false;
        $this->resetForm();
        $this->editingId = $requisition->id;
        $this->items = $requisition->items
            ->map(fn ($item) => ['product_id' => (int) $item->product_id, 'quantity' => (int) $item->quantity])
            ->values()
            ->all();
        $this->showForm = true;
    }

    public function updatedCategoryId(): void
    {
        $this->productId = null;
        $this->resetValidation(['productId']);
    }

    public function addItem(): void
    {
        $this->validate([
            'categoryId' => ['required', 'integer', Rule::exists('product_categories', 'id')->whereNull('deleted_at')],
            'productId' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at')->where('category_id', $this->categoryId),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ], [], $this->attributeNames());

        $productId = (int) $this->productId;
        $quantity = (int) $this->quantity;

        $index = collect($this->items)->search(fn ($item) => (int) $item['product_id'] === $productId);

        if ($index !== false) {
            $this->items[$index]['quantity'] = min(100000, (int) $this->items[$index]['quantity'] + $quantity);
            $this->notify('info', __('requisition.manage.item_merged'));
        } else {
            $this->items[] = ['product_id' => $productId, 'quantity' => $quantity];
        }

        $this->productId = null;
        $this->quantity = 1;
        $this->resetValidation(['items', 'productId', 'quantity']);
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->resetValidation();
    }

    /**
     * @param  string  $action  'save_draft' keeps it editable; 'submit' starts the approval workflow.
     */
    public function save(string $action = 'save_draft'): void
    {
        if (! in_array($action, ['save_draft', 'submit'], true)) {
            return;
        }

        $editing = $this->editingId !== null;
        $requisition = null;

        if ($editing) {
            $requisition = $this->findEditable($this->editingId);
            if (! $requisition) {
                $this->showForm = false;

                return;
            }
        } else {
            $this->authorizeOrFail($this->can('requisitions.create'));
        }

        $validated = $this->validate([
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => __('requisition.manage.items_required'),
            'items.min' => __('requisition.manage.items_required'),
        ], $this->attributeNames());

        $data = [
            // The service expects `details`; only validated, whitelisted keys are passed on.
            'details' => collect($validated['items'])
                ->map(fn ($item) => ['product_id' => (int) $item['product_id'], 'quantity' => (int) $item['quantity']])
                ->values()
                ->all(),
            'remarks' => $validated['remarks'] !== '' ? $validated['remarks'] : null,
            'requisition_date' => $editing
                ? $requisition->requisition_date->format('Y-m-d')
                : now()->format('Y-m-d'),
        ];

        try {
            $service = app(RequisitionService::class);
            $editing
                ? $service->update($data, $requisition->id, $action)
                : $service->store($data, $action);
        } catch (InvalidArgumentException $e) {
            Log::warning('Requisition save rejected', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->addError('form', __('requisition.manage.save_failed'));

            return;
        } catch (Throwable $e) {
            Log::error('Requisition save failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('form', __('requisition.manage.server_error'));

            return;
        }

        $this->showForm = false;
        $this->resetForm();
        unset($this->requisitions, $this->hasAnyRequisition);

        $message = match (true) {
            $action === 'submit' => __('requisition.manage.submitted'),
            $editing => __('requisition.manage.draft_updated'),
            default => __('requisition.manage.draft_saved'),
        };
        $this->notify('success', $message);
    }

    // ------------------------------------------------------------------
    // View
    // ------------------------------------------------------------------

    public function openView(int $id): void
    {
        $requisition = $this->ownRequisitions()->find($id);
        if (! $requisition) {
            $this->notify('error', __('requisition.manage.not_found'));

            return;
        }

        $this->viewingId = $requisition->id;
        $this->showView = true;
    }

    #[Computed]
    public function viewing(): ?Requisition
    {
        if (! $this->viewingId) {
            return null;
        }

        return $this->ownRequisitions()
            ->with([
                'items.product.category',
                'items.lotDetail',
                'requestedBy:id,name',
                'approvalDetails' => fn ($q) => $q->where('status', '!=', 'superceded')->orderBy('serial')->orderBy('id'),
                'approvalDetails.fromUser',
                'approvalDetails.toUser',
            ])
            ->find($this->viewingId);
    }

    /**
     * Approval steps for the view modal, one entry per workflow serial: the user who acted
     * on it, or the users it is waiting for. Mirrors RequisitionController::getDetails().
     *
     * @return array<int, array{state: string, name: string, date: mixed, comments: ?string}>
     */
    #[Computed]
    public function timeline(): array
    {
        $requisition = $this->viewing;
        if (! $requisition) {
            return [];
        }

        $steps = [[
            'state' => 'initiated',
            'name' => $requisition->requestedBy->name ?? '',
            'date' => $requisition->created_at,
            'comments' => null,
        ]];

        foreach ($requisition->approvalDetails->groupBy('serial') as $details) {
            $actor = $details->first(fn ($d) => in_array($d->status, ['approved', 'rejected', 'processed'], true));

            $steps[] = $actor
                ? [
                    'state' => $actor->status,
                    'name' => $actor->fromUser->name ?? '',
                    'date' => $actor->updated_at,
                    'comments' => $actor->comments,
                ]
                : [
                    'state' => 'pending',
                    'name' => $details->map(fn ($d) => $d->toUser->name ?? null)->filter()->unique()->join(', '),
                    'date' => $details->first()->created_at,
                    'comments' => null,
                ];
        }

        return $steps;
    }

    /** Submit a draft for approval straight from the view modal, keeping its items. */
    public function submitDraft(): void
    {
        $requisition = $this->findEditable($this->viewingId);
        if (! $requisition) {
            return;
        }

        $data = [
            'details' => $requisition->items
                ->map(fn ($item) => ['product_id' => (int) $item->product_id, 'quantity' => (int) $item->quantity])
                ->values()
                ->all(),
            'remarks' => null,
            'requisition_date' => $requisition->requisition_date->format('Y-m-d'),
        ];

        try {
            app(RequisitionService::class)->update($data, $requisition->id, 'submit');
        } catch (InvalidArgumentException $e) {
            Log::warning('Requisition submit rejected', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->notify('error', __('requisition.manage.save_failed'));

            return;
        } catch (Throwable $e) {
            Log::error('Requisition submit failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('requisition.manage.server_error'));

            return;
        }

        $this->showView = false;
        $this->viewingId = null;
        unset($this->requisitions, $this->viewing, $this->timeline);
        $this->notify('success', __('requisition.manage.submitted'));
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function confirmDelete(int $id): void
    {
        $requisition = $this->findEditable($id);
        if (! $requisition) {
            return;
        }

        $this->showView = false;
        $this->deletingId = $requisition->id;
        $this->deletingItemsCount = $requisition->items->count();
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $requisition = $this->findEditable($this->deletingId);
        if (! $requisition) {
            $this->showDelete = false;

            return;
        }

        try {
            app(RequisitionService::class)->destroy($requisition->id);
        } catch (Throwable $e) {
            Log::error('Requisition delete failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('requisition.manage.delete_failed'));

            return;
        }

        $this->showDelete = false;
        $this->deletingId = null;
        unset($this->requisitions, $this->hasAnyRequisition);

        // Step back a page if the deleted row was the last one on it.
        if ($this->requisitions->isEmpty() && $this->getPage() > 1) {
            $this->previousPage();
        }

        $this->notify('success', __('requisition.manage.deleted'));
    }

    // ------------------------------------------------------------------
    // Data for the form
    // ------------------------------------------------------------------

    #[Computed]
    public function categories(): Collection
    {
        return ProductCategory::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function productOptions(): Collection
    {
        if (! $this->categoryId) {
            return collect();
        }

        return Product::query()
            ->where('category_id', $this->categoryId)
            ->orderBy('name')
            ->get(['id', 'name', 'product_unit']);
    }

    /** Product details and live stock for the rows in the form, keyed by product id. */
    #[Computed]
    public function itemProducts(): Collection
    {
        $ids = collect($this->items)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $stockService = app(StockService::class);

        return Product::query()
            ->with('category:id,name')
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'product_unit', 'category_id'])
            ->keyBy('id')
            ->map(function (Product $product) use ($stockService) {
                $stock = $stockService->getAggregatedStockList(['product_id' => $product->id]);
                $product->available_quantity = (int) ($stock->first()->total_quantity ?? 0);

                return $product;
            });
    }

    // ------------------------------------------------------------------
    // Presentation helpers used by the view
    // ------------------------------------------------------------------

    protected function canEdit(Requisition $requisition): bool
    {
        return $requisition->status === 'draft' && $this->can('requisitions.index');
    }

    protected function formatDate($date, bool $withTime = false): string
    {
        if (! $date) {
            return '—';
        }

        $date = Carbon::parse($date);
        $text = app()->getLocale() === 'bn'
            ? $date->locale('bn')->translatedFormat($withTime ? 'j F Y, g:i A' : 'j F Y')
            : $date->locale('en')->translatedFormat($withTime ? 'd M Y, h:i A' : 'd M Y');

        return $this->localDigits($text);
    }

    protected function localDigits($value): string
    {
        $value = (string) $value;

        return app()->getLocale() === 'bn'
            ? strtr($value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : $value;
    }

    protected function statusLabel(?string $status): string
    {
        $key = 'requisition.manage.status.' . $status;
        $label = __($key);

        return $label === $key ? ucfirst((string) $status) : $label;
    }

    public function render()
    {
        return view('livewire.requisitions.manage-requisitions', [
            'statuses' => self::STATUSES,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'canCreate' => $this->can('requisitions.create'),
            'hasFilters' => $this->hasFilters(),
            'canEdit' => fn (Requisition $r) => $this->canEdit($r),
            'formatDate' => fn ($date, bool $withTime = false) => $this->formatDate($date, $withTime),
            'digits' => fn ($value) => $this->localDigits($value),
            'statusLabel' => fn (?string $status) => $this->statusLabel($status),
        ]);
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    /** Only the signed-in user's own requisitions are ever reachable (prevents IDOR). */
    private function ownRequisitions(): Builder
    {
        return Requisition::query()->where('requested_by_user_id', Auth::id());
    }

    /** Own + draft + allowed, or null after telling the user why not. */
    private function findEditable(?int $id): ?Requisition
    {
        $this->authorizeOrFail($this->can('requisitions.index'));

        $requisition = $id ? $this->ownRequisitions()->with('items')->find($id) : null;

        if (! $requisition) {
            $this->notify('error', __('requisition.manage.not_found'));

            return null;
        }

        if ($requisition->status !== 'draft') {
            $this->notify('error', __('requisition.manage.not_editable'));

            return null;
        }

        return $requisition;
    }

    private function can(string $route): bool
    {
        return app(MenuPermissionService::class)->allows(Auth::user(), $route);
    }

    private function authorizeOrFail(bool $allowed): void
    {
        abort_unless(Auth::check() && $allowed, 403);
    }

    private function resetForm(): void
    {
        $this->reset('categoryId', 'productId', 'quantity', 'items', 'remarks');
        $this->resetValidation();
        unset($this->itemProducts, $this->productOptions);
    }

    private function notify(string $type, string $message): void
    {
        $this->dispatch('requisition-toast', type: $type, message: $message);
    }

    private function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function toLatinDigits(string $value): string
    {
        return strtr($value, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
    }

    /** @return array<string, string> */
    private function attributeNames(): array
    {
        return [
            'categoryId' => __('requisition.manage.category'),
            'productId' => __('requisition.manage.product'),
            'quantity' => __('requisition.manage.quantity'),
            'items' => __('requisition.manage.items'),
            'items.*.product_id' => __('requisition.manage.product'),
            'items.*.quantity' => __('requisition.manage.quantity'),
            'remarks' => __('requisition.manage.remarks'),
        ];
    }
}
