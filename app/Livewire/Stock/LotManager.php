<?php

namespace App\Livewire\Stock;

use App\Livewire\Concerns\ManagesCrudTable;
use App\Models\FiscalYear;
use App\Models\Lot;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPurchaseType;
use App\Services\LotService;
use Illuminate\Database\Eloquent\Builder;
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
 * Lot management: list with status filter and search; create, view, edit and delete in
 * modals. Saving goes through the existing LotService, so the rules are unchanged:
 * a lot is saved as a draft or submitted for approval, and only drafts can be edited or
 * deleted. Approving / rejecting stays on the "lot requests" screen.
 */
class LotManager extends Component
{
    use ManagesCrudTable, WithPagination;

    public const STATUSES = ['draft', 'pending', 'approved', 'rejected'];

    #[Url(except: '')]
    public string $status = '';

    // ---- Form ----
    public bool $showForm = false;

    #[Locked]
    public ?int $lotId = null;

    public string $lotNumber = '';

    public $fiscalYearId = null;

    public $purchaseTypeId = null;

    public string $entryDate = '';

    public string $remarks = '';

    /** @var array<int, array{product_id:int, brand:string, specification:string, quantity:mixed, unit_price:mixed}> */
    public array $items = [];

    // Item picker
    public $pickCategoryId = null;

    public $pickProductId = null;

    public string $pickSpecification = '';

    public $pickQuantity = 1;

    /** Index of the line being edited through the picker; null while adding a new one. */
    #[Locked]
    public ?int $editingIndex = null;

    /** Page of the item table inside the form (10 lines per page). */
    public int $itemPage = 1;

    private const ITEMS_PER_PAGE = 10;

    // ---- View / delete ----
    public bool $showView = false;

    #[Locked]
    public ?int $viewId = null;

    public bool $showDelete = false;

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteLabel = '';

    protected function permissionRoutes(): array
    {
        return ['lots.index', 'lots.create'];
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

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    // ------------------------------------------------------------------
    // List
    // ------------------------------------------------------------------

    #[Computed]
    public function lots()
    {
        $term = trim($this->search);
        $status = in_array($this->status, self::STATUSES, true) ? $this->status : null;

        return Lot::query()
            ->with(['purchaseType:id,name', 'fiscalYear:id,name'])
            ->withCount('lotDetails')
            ->withSum('lotDetails as total_value', \Illuminate\Support\Facades\DB::raw('quantity * unit_price'))
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($term !== '', function (Builder $query) use ($term) {
                $like = '%' . $term . '%';
                $query->where(function (Builder $inner) use ($like) {
                    $inner->where('lot_number', 'like', $like)
                        ->orWhere('remarks', 'like', $like)
                        ->orWhereHas('purchaseType', fn (Builder $type) => $type->where('name', 'like', $like))
                        ->orWhereHas('lotDetails.product', fn (Builder $product) => $product->where('name', 'like', $like));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    // ------------------------------------------------------------------
    // Create / edit
    // ------------------------------------------------------------------

    public function openCreate(): void
    {
        $this->authorizeAccess();
        $this->resetForm();
        $this->entryDate = now()->format('Y-m-d');
        $this->fiscalYearId = $this->currentFiscalYearId();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeAccess();
        $lot = $this->findDraft($id);
        if (! $lot) {
            return;
        }

        $this->resetForm();
        $this->showView = false;
        $this->lotId = $lot->id;
        $this->lotNumber = (string) $lot->lot_number;
        $this->fiscalYearId = $lot->fiscal_year_id;
        $this->purchaseTypeId = $lot->product_purchase_type_id;
        $this->entryDate = $lot->entry_date ? $lot->entry_date->format('Y-m-d') : '';
        $this->remarks = (string) $lot->remarks;
        $this->items = $lot->lotDetails->map(fn ($detail) => [
            'product_id' => (int) $detail->product_id,
            'brand' => (string) $detail->brand,
            'specification' => (string) $detail->specification,
            'quantity' => (int) $detail->quantity,
            'unit_price' => (string) (float) $detail->unit_price,
        ])->values()->all();
        $this->showForm = true;
    }

    public function updatedPickCategoryId(): void
    {
        $this->pickProductId = null;
        $this->resetValidation('pickProductId');
    }

    public function addItem(): void
    {
        $this->pickSpecification = trim($this->pickSpecification);

        $this->validate([
            'pickCategoryId' => ['required', 'integer', Rule::exists('product_categories', 'id')->whereNull('deleted_at')],
            'pickProductId' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->whereNull('deleted_at')->where('category_id', $this->pickCategoryId),
            ],
            'pickSpecification' => ['nullable', 'string', 'max:191'],
            'pickQuantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'pickCategoryId.required' => __('commontable.required_field', ['attribute' => __('lot.manage.category')]),
            'pickProductId.required' => __('commontable.required_field', ['attribute' => __('lot.table_product')]),
            'pickQuantity.required' => __('commontable.required_field', ['attribute' => __('lot.table_quantity')]),
            'pickQuantity.integer' => __('lot.manage.quantity_invalid'),
            'pickQuantity.min' => __('lot.manage.quantity_invalid'),
        ], $this->attributeNames());

        $productId = (int) $this->pickProductId;
        $quantity = (int) $this->pickQuantity;
        $specification = $this->pickSpecification;

        if ($this->editingIndex !== null && isset($this->items[$this->editingIndex])) {
            // Editing a line: brand and price it already carries (older drafts) are kept.
            $this->items[$this->editingIndex] = array_merge($this->items[$this->editingIndex], [
                'product_id' => $productId,
                'specification' => $specification,
                'quantity' => $quantity,
            ]);
            $this->notify('success', __('lot.manage.item_updated'));
        } else {
            // The same product with the same specification is one line.
            $index = collect($this->items)->search(fn ($item) => (int) $item['product_id'] === $productId
                && mb_strtolower((string) $item['specification']) === mb_strtolower($specification));

            if ($index !== false) {
                $merged = $this->items[$index];
                $merged['quantity'] = (int) $merged['quantity'] + $quantity;
                unset($this->items[$index]);
                array_unshift($this->items, $merged);
                $this->notify('info', __('lot.manage.item_merged'));
            } else {
                // Newest line first.
                array_unshift($this->items, [
                    'product_id' => $productId,
                    'brand' => '',
                    'specification' => $specification,
                    'quantity' => $quantity,
                    'unit_price' => '0',
                ]);
            }
            $this->items = array_values($this->items);
            $this->itemPage = 1;
        }

        $this->resetPicker();
    }

    /** Load a line into the picker so it can be changed. */
    public function editItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $item = $this->items[$index];
        $this->editingIndex = $index;
        $this->pickCategoryId = Product::whereKey($item['product_id'])->value('category_id');
        $this->pickProductId = (int) $item['product_id'];
        $this->pickSpecification = (string) $item['specification'];
        $this->pickQuantity = (int) $item['quantity'];
        $this->resetValidation();
    }

    public function cancelItemEdit(): void
    {
        $this->resetPicker();
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->resetPicker();
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

    private function resetPicker(): void
    {
        $this->reset('pickProductId', 'pickSpecification', 'editingIndex');
        $this->pickQuantity = 1;
        $this->resetValidation();
    }

    /**
     * @param  string  $action  'save_draft' keeps the lot editable; 'submit' sends it for approval.
     */
    public function save(string $action = 'save_draft'): void
    {
        $this->authorizeAccess();
        if (! in_array($action, ['save_draft', 'submit'], true)) {
            return;
        }

        $editing = $this->lotId !== null;
        if ($editing && ! $this->findDraft($this->lotId)) {
            $this->showForm = false;

            return;
        }

        $this->lotNumber = trim($this->lotNumber);

        $data = $this->validate([
            'lotNumber' => ['required', 'string', 'max:191', Rule::unique('lots', 'lot_number')->ignore($this->lotId)],
            'fiscalYearId' => ['required', 'integer', Rule::exists('fiscal_years', 'id')],
            'purchaseTypeId' => ['required', 'integer', Rule::exists('product_purchase_types', 'id')->whereNull('deleted_at')],
            'entryDate' => ['required', 'date_format:Y-m-d'],
            'remarks' => ['nullable', 'string', 'max:191'],
            'items' => ['required', 'array', 'min:1', 'max:300'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.brand' => ['nullable', 'string', 'max:191'],
            'items.*.specification' => ['nullable', 'string', 'max:191'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ], [
            'lotNumber.required' => __('commontable.required_field', ['attribute' => __('lot.lot_number')]),
            'lotNumber.unique' => __('lot.manage.exists'),
            'fiscalYearId.required' => __('commontable.required_field', ['attribute' => __('lot.fiscal_year')]),
            'purchaseTypeId.required' => __('commontable.required_field', ['attribute' => __('lot.purchase_type')]),
            'entryDate.required' => __('commontable.required_field', ['attribute' => __('lot.entry_date')]),
            'items.required' => __('lot.manage.items_required'),
            'items.min' => __('lot.manage.items_required'),
        ], $this->attributeNames());

        $payload = [
            'lot_number' => $data['lotNumber'],
            'fiscal_year_id' => (int) $data['fiscalYearId'],
            'product_purchase_type_id' => (int) $data['purchaseTypeId'],
            'entry_date' => $data['entryDate'],
            'remarks' => trim((string) $data['remarks']) !== '' ? trim($data['remarks']) : null,
            // Only the validated, whitelisted keys are passed on.
            'details' => collect($data['items'])->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'brand' => trim((string) ($item['brand'] ?? '')) !== '' ? trim($item['brand']) : null,
                'specification' => trim((string) ($item['specification'] ?? '')) !== '' ? trim($item['specification']) : null,
                'quantity' => (int) $item['quantity'],
                // The price field was dropped from the form; lines carry 0 unless an older
                // draft already had a price, which is kept.
                'unit_price' => round((float) ($item['unit_price'] ?? 0), 2),
            ])->values()->all(),
        ];

        try {
            $service = app(LotService::class);
            $editing ? $service->update($payload, $this->lotId, $action) : $service->store($payload, $action);
        } catch (InvalidArgumentException $e) {
            Log::warning('Lot save rejected', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->addError('form', __('lot.manage.save_failed'));
            $this->notify('error', __('lot.manage.save_failed'));

            return;
        } catch (Throwable $e) {
            Log::error('Lot save failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('form', __('commontable.server_error'));
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        $this->showForm = false;
        $this->resetForm();
        if (! $editing) {
            $this->resetPage();
        }
        unset($this->lots);

        $this->notify('success', __(match (true) {
            $action === 'submit' => 'lot.manage.submitted',
            $editing => 'lot.manage.draft_updated',
            default => 'lot.manage.draft_saved',
        }));
    }

    // ------------------------------------------------------------------
    // View
    // ------------------------------------------------------------------

    public function openView(int $id): void
    {
        $this->authorizeAccess();
        if (! Lot::whereKey($id)->exists()) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->viewId = $id;
        $this->showView = true;
    }

    #[Computed]
    public function viewing(): ?Lot
    {
        return $this->viewId
            ? Lot::with(['lotDetails.product.category', 'purchaseType', 'fiscalYear'])->find($this->viewId)
            : null;
    }

    /** Send a draft for approval straight from the view modal, with its lines unchanged. */
    public function submitDraft(): void
    {
        $this->authorizeAccess();
        $lot = $this->viewId ? $this->findDraft($this->viewId) : null;
        if (! $lot) {
            return;
        }

        $payload = [
            'lot_number' => $lot->lot_number,
            'fiscal_year_id' => $lot->fiscal_year_id,
            'product_purchase_type_id' => $lot->product_purchase_type_id,
            'entry_date' => $lot->entry_date ? $lot->entry_date->format('Y-m-d') : now()->format('Y-m-d'),
            'remarks' => $lot->remarks,
            'details' => $lot->lotDetails->map(fn ($detail) => [
                'product_id' => (int) $detail->product_id,
                'brand' => $detail->brand,
                'specification' => $detail->specification,
                'quantity' => (int) $detail->quantity,
                'unit_price' => (float) $detail->unit_price,
            ])->values()->all(),
        ];

        try {
            app(LotService::class)->update($payload, $lot->id, 'submit');
        } catch (InvalidArgumentException $e) {
            Log::warning('Lot submit rejected', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->notify('error', __('lot.manage.save_failed'));

            return;
        } catch (Throwable $e) {
            Log::error('Lot submit failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        $this->showView = false;
        $this->viewId = null;
        unset($this->lots, $this->viewing);
        $this->notify('success', __('lot.manage.submitted'));
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function confirmDelete(int $id): void
    {
        $this->authorizeAccess();
        $lot = $this->findDraft($id);
        if (! $lot) {
            return;
        }

        $this->showView = false;
        $this->deleteId = $lot->id;
        $this->deleteLabel = (string) $lot->lot_number;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $this->authorizeAccess();
        $this->showDelete = false;
        $lot = $this->deleteId ? $this->findDraft($this->deleteId) : null;
        if (! $lot) {
            return;
        }

        try {
            app(LotService::class)->destroy($lot->id);
        } catch (Throwable $e) {
            Log::error('Lot delete failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('lot.manage.delete_failed'));

            return;
        }

        $this->deleteId = null;
        unset($this->lots);
        if ($this->lots->isEmpty() && $this->getPage() > 1) {
            $this->previousPage();
            unset($this->lots);
        }
        $this->notify('success', __('lot.manage.deleted'));
    }

    // ------------------------------------------------------------------
    // Options and helpers for the view
    // ------------------------------------------------------------------

    #[Computed]
    public function fiscalYears(): Collection
    {
        return FiscalYear::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $this->fiscalYearId))
            ->orderByDesc('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function purchaseTypes(): Collection
    {
        return ProductPurchaseType::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function categoryOptions(): Collection
    {
        return ProductCategory::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function productOptions(): Collection
    {
        return $this->pickCategoryId
            ? Product::query()->where('category_id', $this->pickCategoryId)->orderBy('name')->get(['id', 'name', 'product_unit'])
            : collect();
    }

    /** Products of the lines in the form, keyed by id. */
    #[Computed]
    public function itemProducts(): Collection
    {
        $ids = collect($this->items)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->values();

        return $ids->isEmpty()
            ? collect()
            : Product::query()->with('category:id,name')->whereIn('id', $ids)->get(['id', 'name', 'product_unit', 'category_id'])->keyBy('id');
    }

    public function render()
    {
        return view('livewire.stock.lot-manager', $this->tableViewData() + [
            'rows' => $this->lots,
            'statuses' => self::STATUSES,
            'money' => fn ($value) => $this->localDigits(number_format((float) $value, 2)),
            'formatDate' => fn ($date) => $date ? $this->formatDate($date) : '—',
            'statusLabel' => fn (?string $status) => $this->statusLabel($status),
            // Lines of the current item page, keyed by their real index in $items.
            'pageItems' => array_slice($this->items, ($this->itemPage - 1) * self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE, true),
            'itemPages' => $this->itemPageCount(),
        ]);
    }

    private function formatDate($date): string
    {
        $date = \Illuminate\Support\Carbon::parse($date);

        return app()->getLocale() === 'bn'
            ? bangla_date($date->timestamp, 'en')
            : $date->format('d M, Y');
    }

    private function statusLabel(?string $status): string
    {
        $key = 'lot.manage.status.' . $status;
        $label = __($key);

        return $label === $key ? ucfirst((string) $status) : $label;
    }

    /** A lot that exists and is still a draft, or null after telling the user why not. */
    private function findDraft(int $id): ?Lot
    {
        $lot = Lot::with('lotDetails')->find($id);

        if (! $lot) {
            $this->notify('error', __('commontable.not_found'));

            return null;
        }

        if ($lot->status !== 'draft') {
            $this->notify('error', __('lot.manage.not_editable'));

            return null;
        }

        return $lot;
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
        $this->reset(
            'lotId', 'lotNumber', 'fiscalYearId', 'purchaseTypeId', 'entryDate', 'remarks', 'items',
            'pickCategoryId', 'pickProductId', 'pickSpecification', 'editingIndex'
        );
        $this->pickQuantity = 1;
        $this->itemPage = 1;
        $this->resetValidation();
    }

    /** @return array<string, string> */
    private function attributeNames(): array
    {
        return [
            'lotNumber' => __('lot.lot_number'),
            'fiscalYearId' => __('lot.fiscal_year'),
            'purchaseTypeId' => __('lot.purchase_type'),
            'entryDate' => __('lot.entry_date'),
            'remarks' => __('lot.remarks'),
            'items' => __('lot.manage.items'),
            'items.*.product_id' => __('lot.table_product'),
            'items.*.brand' => __('lot.brand'),
            'items.*.specification' => __('lot.specification'),
            'items.*.quantity' => __('lot.table_quantity'),
            'items.*.unit_price' => __('lot.manage.unit_price'),
            'pickCategoryId' => __('lot.manage.category'),
            'pickProductId' => __('lot.table_product'),
            'pickSpecification' => __('lot.specification'),
            'pickQuantity' => __('lot.table_quantity'),
        ];
    }
}
