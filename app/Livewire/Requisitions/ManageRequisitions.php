<?php

namespace App\Livewire\Requisitions;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Services\MenuPermissionService;
use App\Services\RequisitionService;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    /** Role "General User" (ব্যবহারকারী), as in cnf_role. */
    private const GENERAL_USER_ROLE_ID = 5;

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

    /** Row of `items` loaded into the picker for editing; null while adding. */
    public ?int $editingIndex = null;

    /** Page of the requested-items table in the form. */
    public int $itemPage = 1;

    private const ITEMS_PER_PAGE = 7;

    /** Page of the items table in the view dialog. */
    public int $viewItemPage = 1;

    private const VIEW_ITEMS_PER_PAGE = 5;

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

    /** Choosing a category clears its error at once (the product list changes with it). */
    public function updatedCategoryId(): void
    {
        $this->productId = null;
        $this->resetValidation($this->categoryId ? ['categoryId', 'productId'] : ['productId']);
    }

    /** Choosing a product clears its error at once. */
    public function updatedProductId(): void
    {
        if ($this->productId) {
            $this->resetValidation(['productId']);
        }
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
        ], $this->validationMessages(), $this->attributeNames());

        $productId = (int) $this->productId;
        $quantity = (int) $this->quantity;
        $editing = $this->editingIndex !== null && isset($this->items[$this->editingIndex]);

        // Another row that already holds this product (the row being edited does not count).
        $index = collect($this->items)->search(fn ($item, $key) => (int) $item['product_id'] === $productId
            && ! ($editing && $key === $this->editingIndex));

        $shown = null; // row to bring into view
        if ($editing && $index === false) {
            $this->items[$this->editingIndex] = ['product_id' => $productId, 'quantity' => $quantity];
            $shown = $this->editingIndex;
            $this->notify('success', __('requisition.manage.item_updated'));
        } elseif ($index !== false) {
            // One row per product: the quantity goes to the existing row.
            $this->items[$index]['quantity'] = min(100000, (int) $this->items[$index]['quantity'] + $quantity);
            if ($editing) {
                unset($this->items[$this->editingIndex]);
                $this->items = array_values($this->items);
            }
            $shown = collect($this->items)->search(fn ($item) => (int) $item['product_id'] === $productId);
            $this->notify('info', __('requisition.manage.item_merged'));
        } else {
            $this->items[] = ['product_id' => $productId, 'quantity' => $quantity];
            $shown = count($this->items) - 1;
        }

        $this->showItemRow((int) $shown);
        $this->resetPicker();
        $this->resetValidation(['items', 'productId', 'quantity']);
    }

    /** Loads a row into the picker so its product or quantity can be changed. */
    public function editItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $item = $this->items[$index];
        $this->editingIndex = $index;
        $this->categoryId = Product::whereKey($item['product_id'])->value('category_id');
        $this->productId = (int) $item['product_id'];
        $this->quantity = max(1, (int) $item['quantity']);
        unset($this->productOptions);
        $this->resetValidation();
    }

    public function cancelItemEdit(): void
    {
        $this->resetPicker();
        $this->resetValidation(['categoryId', 'productId', 'quantity']);
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->resetPicker();
        $this->resetValidation();
        $this->itemPage = max(1, min($this->itemPage, $this->itemPageCount()));
    }

    public function gotoItemPage(int $page): void
    {
        $this->itemPage = max(1, min($page, $this->itemPageCount()));
    }

    private function itemPageCount(): int
    {
        return max(1, (int) ceil(count($this->items) / self::ITEMS_PER_PAGE));
    }

    /** Opens the page of the items table that holds the given row. */
    private function showItemRow(int $index): void
    {
        $this->itemPage = max(1, min(intdiv(max(0, $index), self::ITEMS_PER_PAGE) + 1, $this->itemPageCount()));
    }

    /** Clears the picker but keeps the chosen category, so the next product is quick to add. */
    private function resetPicker(): void
    {
        $this->editingIndex = null;
        $this->productId = null;
        $this->quantity = 1;
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

        try {
            $validated = $this->validate([
                'items' => ['required', 'array', 'min:1', 'max:200'],
                'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->whereNull('deleted_at')],
                'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
                'remarks' => ['nullable', 'string', 'max:1000'],
            ], $this->validationMessages(), $this->attributeNames());
        } catch (ValidationException $e) {
            // The row with the problem may be on another page of the items table: open it.
            foreach (array_keys($e->errors()) as $key) {
                if (preg_match('/^items\.(\d+)\./', $key, $match)) {
                    $this->showItemRow((int) $match[1]);
                    break;
                }
            }

            throw $e;
        }

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
        unset($this->viewing, $this->previousRequests);
        $this->viewItemPage = 1;
        $this->showView = true;
    }

    public function gotoViewItemPage(int $page): void
    {
        $count = $this->viewing ? $this->viewing->items->count() : 0;
        $last = max(1, (int) ceil($count / self::VIEW_ITEMS_PER_PAGE));
        $this->viewItemPage = max(1, min($page, $last));
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
                'items.product.subCategory',
                'items.lotDetail',
                'requestedBy:id,name',
                'approvalDetails' => fn ($q) => $q->where('status', '!=', 'superceded')->orderBy('serial')->orderBy('id'),
                'approvalDetails.fromUser',
                'approvalDetails.toUser',
            ])
            ->find($this->viewingId);
    }

    /**
     * The requester's latest earlier requisition for each product of the one being viewed:
     * its date and the quantity asked for, keyed by product id. Submitted requisitions only
     * (drafts do not count), and only those made before this one.
     */
    #[Computed]
    public function previousRequests(): Collection
    {
        $viewing = $this->viewing;
        if (! $viewing) {
            return collect();
        }

        return RequisitionItem::query()
            ->join('requisitions', 'requisitions.id', '=', 'requisition_items.requisition_id')
            ->whereIn('requisition_items.product_id', $viewing->items->pluck('product_id')->unique())
            ->where('requisitions.requested_by_user_id', $viewing->requested_by_user_id)
            ->where('requisitions.id', '<', $viewing->id)
            ->where('requisitions.status', '!=', 'draft')
            ->whereNull('requisitions.deleted_at')
            ->whereNull('requisition_items.deleted_at')
            ->groupBy('requisition_items.product_id', 'requisitions.id', 'requisitions.requisition_date')
            ->orderByDesc('requisitions.requisition_date')
            ->orderByDesc('requisitions.id')
            ->selectRaw('requisition_items.product_id, requisitions.requisition_date as last_date, sum(requisition_items.quantity) as last_quantity')
            ->toBase()
            ->get()
            ->unique('product_id') // newest first, so the first row per product is the latest
            ->keyBy('product_id');
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

        // Date of this user's latest earlier requisition for each product: submitted ones only,
        // and not the requisition that is being edited.
        $previousDates = RequisitionItem::query()
            ->join('requisitions', 'requisitions.id', '=', 'requisition_items.requisition_id')
            ->whereIn('requisition_items.product_id', $ids)
            ->where('requisitions.requested_by_user_id', Auth::id())
            ->where('requisitions.status', '!=', 'draft')
            ->whereNull('requisitions.deleted_at')
            ->when($this->editingId, fn ($query) => $query->where('requisitions.id', '!=', $this->editingId))
            ->whereNull('requisition_items.deleted_at')
            ->groupBy('requisition_items.product_id', 'requisitions.id', 'requisitions.requisition_date')
            ->orderByDesc('requisitions.requisition_date')
            ->orderByDesc('requisitions.id')
            ->selectRaw('requisition_items.product_id, requisitions.requisition_date as last_date, sum(requisition_items.quantity) as last_quantity')
            ->toBase()
            ->get()
            ->unique('product_id') // newest requisition first, so the first row per product is the latest
            ->keyBy('product_id');

        return Product::query()
            ->with('category:id,name')
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'product_unit', 'category_id'])
            ->keyBy('id')
            ->map(function (Product $product) use ($stockService, $previousDates) {
                $stock = $stockService->getAggregatedStockList(['product_id' => $product->id]);
                $product->available_quantity = (int) ($stock->first()->total_quantity ?? 0);
                $previous = $previousDates->get($product->id);
                $product->previous_requisition_date = $previous->last_date ?? null;
                $product->previous_requisition_quantity = $previous ? (int) $previous->last_quantity : null;

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
            // Rows of the current page of the items table; the keys stay the rows' real indexes.
            'pageItems' => array_slice($this->items, ($this->itemPage - 1) * self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE, true),
            'itemPages' => $this->itemPageCount(),
            'itemsPerPage' => self::ITEMS_PER_PAGE,
            'viewItemsPerPage' => self::VIEW_ITEMS_PER_PAGE,
            // Today's weekday and date, shown in the header of the view dialog.
            'todayLabel' => $this->localDigits(now()->locale(app()->getLocale() === 'bn' ? 'bn' : 'en')->translatedFormat(app()->getLocale() === 'bn' ? 'l, j F Y' : 'l, d M Y')),
            // Everyone sees how many are in stock except the General User role, who only sees whether it is.
            'showStockCount' => (int) Auth::user()?->role_id !== self::GENERAL_USER_ROLE_ID,
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
        $this->reset('categoryId', 'productId', 'quantity', 'items', 'remarks', 'editingIndex', 'itemPage');
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
    /** Messages in the page language for the picker and for the item rows. */
    private function validationMessages(): array
    {
        $required = fn (string $attribute) => __('commontable.required_field', ['attribute' => $attribute]);
        $quantity = __('requisition.manage.quantity_invalid');

        return [
            'categoryId.required' => $required(__('requisition.manage.category')),
            'categoryId.integer' => __('requisition.manage.category_invalid'),
            'categoryId.exists' => __('requisition.manage.category_invalid'),
            'productId.required' => $required(__('requisition.manage.product')),
            'productId.integer' => __('requisition.manage.product_invalid'),
            'productId.exists' => __('requisition.manage.product_invalid'),
            'quantity.required' => $required(__('requisition.manage.quantity')),
            'quantity.integer' => $quantity,
            'quantity.min' => $quantity,
            'quantity.max' => $quantity,
            'items.required' => __('requisition.manage.items_required'),
            'items.min' => __('requisition.manage.items_required'),
            'items.max' => __('requisition.manage.items_too_many'),
            'items.*.product_id.required' => __('requisition.manage.product_invalid'),
            'items.*.product_id.integer' => __('requisition.manage.product_invalid'),
            'items.*.product_id.exists' => __('requisition.manage.product_invalid'),
            'items.*.product_id.distinct' => __('requisition.manage.product_duplicate'),
            'items.*.quantity.required' => $required(__('requisition.manage.quantity')),
            'items.*.quantity.integer' => $quantity,
            'items.*.quantity.min' => $quantity,
            'items.*.quantity.max' => $quantity,
            'remarks.max' => __('requisition.manage.remarks_too_long'),
        ];
    }

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
