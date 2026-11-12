<?php

namespace App\Livewire\Catalog;

use App\Livewire\Concerns\ManagesCrudTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Services\ProductCategoryService;
use App\Services\ProductSubCategoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * Category management: one row per category (grouped by its id) with its sub-categories
 * nested underneath. Categories and sub-categories are created, edited and deleted in
 * modals; saving goes through the existing category / sub-category services.
 */
class CategoryManager extends Component
{
    use ManagesCrudTable, WithPagination;

    /** Category ids whose sub-category list is open. */
    public array $expanded = [];

    // ---- Category form ----
    public bool $showCategoryForm = false;

    #[Locked]
    public ?int $categoryId = null;

    public string $categoryName = '';

    public string $categoryDescription = '';

    // ---- Sub-category form ----
    public bool $showSubForm = false;

    #[Locked]
    public ?int $subId = null;

    public $subParentId = null;

    public string $subName = '';

    public string $subCode = '';

    // ---- Delete confirmation ----
    public bool $showDelete = false;

    #[Locked]
    public string $deleteType = '';

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteLabel = '';

    protected function permissionRoutes(): array
    {
        return ['categories.create', 'categories.index'];
    }

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    // ------------------------------------------------------------------
    // List
    // ------------------------------------------------------------------

    #[Computed]
    public function categories()
    {
        $term = trim($this->search);

        return ProductCategory::query()
            ->with(['subCategories' => fn ($query) => $query->withCount('products')->orderBy('name')])
            ->withCount(['subCategories', 'products'])
            ->when($term !== '', function (Builder $query) use ($term) {
                $like = '%' . $term . '%';
                $query->where(function (Builder $inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('subCategories', fn (Builder $sub) => $sub
                            ->where('name', 'like', $like)
                            ->orWhere('code', 'like', $like));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    public function toggle(int $categoryId): void
    {
        $this->expanded = in_array($categoryId, $this->expanded, true)
            ? array_values(array_diff($this->expanded, [$categoryId]))
            : [...$this->expanded, $categoryId];
    }

    // ------------------------------------------------------------------
    // Category create / edit
    // ------------------------------------------------------------------

    public function openCategoryCreate(): void
    {
        $this->authorizeAccess();
        $this->resetCategoryForm();
        $this->showCategoryForm = true;
    }

    public function openCategoryEdit(int $id): void
    {
        $this->authorizeAccess();
        $category = ProductCategory::find($id);
        if (! $category) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->resetCategoryForm();
        $this->categoryId = $category->id;
        $this->categoryName = (string) $category->name;
        $this->categoryDescription = (string) $category->description;
        $this->showCategoryForm = true;
    }

    public function saveCategory(): void
    {
        $this->authorizeAccess();
        $this->categoryName = trim($this->categoryName);

        $data = $this->validate([
            'categoryName' => ['required', 'string', 'max:191', Rule::unique('product_categories', 'name')->ignore($this->categoryId)],
            'categoryDescription' => ['nullable', 'string', 'max:1000'],
        ], [
            'categoryName.unique' => __('category.manage.category_exists'),
            'categoryName.required' => __('commontable.required_field', ['attribute' => __('category.manage.category_name')]),
        ], [
            'categoryName' => __('category.manage.category_name'),
            'categoryDescription' => __('category.manage.description'),
        ]);

        $payload = [
            'name' => $data['categoryName'],
            'description' => trim((string) $data['categoryDescription']) !== '' ? trim($data['categoryDescription']) : null,
        ];
        $editing = $this->categoryId !== null;

        try {
            $service = app(ProductCategoryService::class);
            $editing ? $service->update($payload, $this->categoryId) : $service->store($payload);
        } catch (InvalidArgumentException $e) {
            Log::warning('Category save rejected', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->addError('categoryForm', __('category.manage.save_failed'));
            $this->notify('error', __('category.manage.save_failed'));

            return;
        } catch (Throwable $e) {
            Log::error('Category save failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('categoryForm', __('commontable.server_error'));
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        $this->showCategoryForm = false;
        $this->resetCategoryForm();
        if (! $editing) {
            $this->resetPage(); // newest first: show the new category
        }
        unset($this->categories);
        $this->notify('success', __($editing ? 'category.manage.category_updated' : 'category.manage.category_created'));
    }

    // ------------------------------------------------------------------
    // Sub-category create / edit
    // ------------------------------------------------------------------

    public function openSubCreate(?int $categoryId = null): void
    {
        $this->authorizeAccess();
        $this->resetSubForm();
        $this->subParentId = $categoryId;
        // Shown read-only; the final code is assigned again at save time.
        $this->subCode = $this->nextSubCode();
        $this->showSubForm = true;
    }

    public function openSubEdit(int $id): void
    {
        $this->authorizeAccess();
        $sub = ProductSubCategory::find($id);
        if (! $sub) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->resetSubForm();
        $this->subId = $sub->id;
        $this->subParentId = $sub->category_id;
        $this->subName = (string) $sub->name;
        $this->subCode = (string) $sub->code;
        $this->showSubForm = true;
    }

    public function saveSub(): void
    {
        $this->authorizeAccess();
        $this->subName = trim($this->subName);

        // The code is created by the system, never typed: an existing sub-category keeps
        // its code, a new one (or one that has none yet) gets the next free code.
        $existingCode = $this->subId ? trim((string) ProductSubCategory::whereKey($this->subId)->value('code')) : '';
        $this->subCode = $existingCode !== '' ? $existingCode : $this->nextSubCode();

        $data = $this->validate([
            'subParentId' => ['required', 'integer', Rule::exists('product_categories', 'id')->whereNull('deleted_at')],
            'subName' => [
                'required',
                'string',
                'max:191',
                // The same name may exist under another category, but not twice in one.
                Rule::unique('product_sub_categories', 'name')
                    ->where('category_id', $this->subParentId)
                    ->whereNull('deleted_at')
                    ->ignore($this->subId),
            ],
            'subCode' => ['nullable', 'string', 'max:100'],
        ], [
            'subName.unique' => __('category.manage.sub_exists'),
            'subName.required' => __('commontable.required_field', ['attribute' => __('category.manage.sub_name')]),
            'subParentId.required' => __('commontable.required_field', ['attribute' => __('category.manage.category')]),
        ], [
            'subParentId' => __('category.manage.category'),
            'subName' => __('category.manage.sub_name'),
            'subCode' => __('category.manage.code'),
        ]);

        $payload = [
            'category_id' => (int) $data['subParentId'],
            'name' => $data['subName'],
            'code' => $data['subCode'] !== '' ? $data['subCode'] : null,
        ];
        $editing = $this->subId !== null;

        try {
            $service = app(ProductSubCategoryService::class);
            $editing ? $service->update($payload, $this->subId) : $service->store($payload);
        } catch (InvalidArgumentException $e) {
            Log::warning('Sub-category save rejected', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->addError('subForm', __('category.manage.save_failed'));
            $this->notify('error', __('category.manage.save_failed'));

            return;
        } catch (Throwable $e) {
            Log::error('Sub-category save failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('subForm', __('commontable.server_error'));
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        // Keep the parent open so the result is visible straight away.
        if (! in_array($payload['category_id'], $this->expanded, true)) {
            $this->expanded[] = $payload['category_id'];
        }

        $this->showSubForm = false;
        $this->resetSubForm();
        unset($this->categories);
        $this->notify('success', __($editing ? 'category.manage.sub_updated' : 'category.manage.sub_created'));
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function confirmDelete(string $type, int $id): void
    {
        $this->authorizeAccess();
        $record = match ($type) {
            'category' => ProductCategory::find($id),
            'sub' => ProductSubCategory::find($id),
            default => null,
        };

        if (! $record) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->deleteType = $type;
        $this->deleteId = $record->id;
        $this->deleteLabel = (string) $record->name;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $this->authorizeAccess();
        $type = $this->deleteType;
        $id = $this->deleteId;
        $this->showDelete = false;

        if (! $id || ! in_array($type, ['category', 'sub'], true)) {
            return;
        }

        // Anything still in use is kept, so products never lose their category.
        if ($type === 'category') {
            if (ProductSubCategory::where('category_id', $id)->exists()) {
                $this->notify('error', __('category.manage.category_has_subs'));

                return;
            }
            if (Product::where('category_id', $id)->exists()) {
                $this->notify('error', __('category.manage.category_has_products'));

                return;
            }
        } elseif (Product::where('sub_category_id', $id)->exists()) {
            $this->notify('error', __('category.manage.sub_has_products'));

            return;
        }

        try {
            $type === 'category'
                ? app(ProductCategoryService::class)->destroy($id)
                : app(ProductSubCategoryService::class)->destroy($id);
        } catch (Throwable $e) {
            Log::error('Category delete failed', ['user_id' => Auth::id(), 'type' => $type, 'exception' => $e]);
            $this->notify('error', __('category.manage.delete_failed'));

            return;
        }

        $this->deleteId = null;
        unset($this->categories);

        // Step back if the last row of the last page was removed.
        if ($this->categories->isEmpty() && $this->getPage() > 1) {
            $this->previousPage();
            unset($this->categories);
        }

        $this->notify('success', __($type === 'category' ? 'category.manage.category_deleted' : 'category.manage.sub_deleted'));
    }

    // ------------------------------------------------------------------

    /** All categories, for the parent dropdown in the sub-category form. */
    #[Computed]
    public function categoryOptions()
    {
        return ProductCategory::query()->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.catalog.category-manager', $this->tableViewData() + [
            'rows' => $this->categories,
        ]);
    }

    /** Next free sub-category code, e.g. SC-0031. */
    private function nextSubCode(): string
    {
        $number = (int) ProductSubCategory::withTrashed()->max('id') + 1;

        do {
            $code = 'SC-' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
            $number++;
        } while (ProductSubCategory::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    private function resetCategoryForm(): void
    {
        $this->reset('categoryId', 'categoryName', 'categoryDescription');
        $this->resetValidation();
    }

    private function resetSubForm(): void
    {
        $this->reset('subId', 'subParentId', 'subName', 'subCode');
        $this->resetValidation();
    }
}
