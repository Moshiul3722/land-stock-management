<?php

namespace App\Livewire\Catalog;

use App\Livewire\Concerns\ManagesCrudTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\ProductUnit;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
 * Product management: list with search and category filter; create, edit and delete in
 * modals. Saving goes through the existing ProductService.
 */
class ProductManager extends Component
{
    use ManagesCrudTable, WithPagination;

    #[Url(as: 'category', except: '')]
    public string $categoryFilter = '';

    public bool $showForm = false;

    #[Locked]
    public ?int $productId = null;

    public string $name = '';

    /** The product's name in Bangla (optional). */
    public string $nameBn = '';

    public string $description = '';

    public $categoryId = null;

    public $subCategoryId = null;

    public string $unit = '';

    /** Products with a name close to the one typed (shown as a warning before saving). */
    #[Locked]
    public array $similarNames = [];

    /** "Yes, this is a different product": save even though a similar name exists. */
    public bool $confirmSimilar = false;

    public bool $showDelete = false;

    #[Locked]
    public ?int $deleteId = null;

    #[Locked]
    public string $deleteLabel = '';

    protected function permissionRoutes(): array
    {
        return ['products.create', 'products.index'];
    }

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        $term = trim($this->search);
        $categoryId = ctype_digit($this->categoryFilter) ? (int) $this->categoryFilter : null;

        return Product::query()
            ->with(['category:id,name', 'subCategory:id,name'])
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->when($term !== '', function (Builder $query) use ($term) {
                $like = '%' . $term . '%';
                $query->where(function (Builder $inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('name_bn', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('product_unit', 'like', $like)
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                        ->orWhereHas('subCategory', fn (Builder $sub) => $sub->where('name', 'like', $like));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    // ---- Form ----

    public function openCreate(): void
    {
        $this->authorizeAccess();
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeAccess();
        $product = Product::find($id);
        if (! $product) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->resetForm();
        $this->productId = $product->id;
        $this->name = (string) $product->name;
        $this->nameBn = (string) $product->name_bn;
        $this->description = (string) $product->description;
        $this->categoryId = $product->category_id;
        $this->subCategoryId = $product->sub_category_id;
        $this->unit = (string) $product->product_unit;
        $this->showForm = true;
    }

    /** A different category has different sub-categories. */
    public function updatedCategoryId(): void
    {
        $this->subCategoryId = null;
        $this->resetValidation('subCategoryId');
    }

    public function save(): void
    {
        $this->authorizeAccess();
        $this->name = trim($this->name);
        $this->nameBn = trim($this->nameBn);
        $this->subCategoryId = $this->subCategoryId ?: null;

        $data = $this->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('products', 'name')->ignore($this->productId)],
            'nameBn' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
            'categoryId' => ['required', 'integer', Rule::exists('product_categories', 'id')->whereNull('deleted_at')],
            'subCategoryId' => [
                'nullable',
                'integer',
                Rule::exists('product_sub_categories', 'id')->where('category_id', $this->categoryId)->whereNull('deleted_at'),
            ],
            'unit' => ['required', 'string', 'max:50'],
        ], [
            'name.required' => __('commontable.required_field', ['attribute' => __('product.manage.name_en')]),
            'name.unique' => __('product.manage.exists'),
            'categoryId.required' => __('commontable.required_field', ['attribute' => __('product.manage.category')]),
            'unit.required' => __('commontable.required_field', ['attribute' => __('product.manage.unit')]),
        ], [
            'name' => __('product.manage.name_en'),
            'nameBn' => __('product.manage.name_bn'),
            'description' => __('product.description'),
            'categoryId' => __('product.manage.category'),
            'subCategoryId' => __('product.manage.sub_category'),
            'unit' => __('product.manage.unit'),
        ]);

        // Duplicates: the same name written differently (case, spaces, punctuation) is refused;
        // a name only a letter or two away (e.g. "Toilate" / "Toilet") needs a confirmation.
        [$same, $similar] = $this->lookAlikes($data['name'], $data['nameBn']);
        if ($same) {
            $this->addError('name', __('product.manage.exists_as', ['name' => $same]));

            return;
        }
        if ($similar && ! $this->confirmSimilar) {
            $this->similarNames = $similar;
            $this->addError('name', __('product.manage.similar_exists', ['names' => implode(', ', $similar)]));

            return;
        }

        $payload = [
            'name' => $data['name'],
            'name_bn' => $data['nameBn'] !== '' ? $data['nameBn'] : null,
            'description' => trim((string) $data['description']) !== '' ? trim($data['description']) : null,
            'category_id' => (int) $data['categoryId'],
            'sub_category_id' => $data['subCategoryId'] ? (int) $data['subCategoryId'] : null,
            'product_unit' => $data['unit'],
        ];
        $editing = $this->productId !== null;

        try {
            $service = app(ProductService::class);
            $editing ? $service->update($payload, $this->productId) : $service->store($payload);
        } catch (InvalidArgumentException $e) {
            Log::warning('Product save rejected', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->addError('form', __('product.manage.save_failed'));
            $this->notify('error', __('product.manage.save_failed'));

            return;
        } catch (Throwable $e) {
            Log::error('Product save failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->addError('form', __('commontable.server_error'));
            $this->notify('error', __('commontable.server_error'));

            return;
        }

        $this->showForm = false;
        $this->resetForm();
        if (! $editing) {
            $this->resetPage();
        }
        unset($this->products);
        $this->notify('success', __($editing ? 'product.manage.updated' : 'product.manage.created'));
    }

    // ---- Delete ----

    public function confirmDelete(int $id): void
    {
        $this->authorizeAccess();
        $product = Product::find($id);
        if (! $product) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }

        $this->deleteId = $product->id;
        $this->deleteLabel = (string) $product->name;
        $this->showDelete = true;
    }

    public function delete(): void
    {
        $this->authorizeAccess();
        $this->showDelete = false;
        $id = $this->deleteId;
        if (! $id || ! Product::whereKey($id)->exists()) {
            return;
        }

        // A product that appears in stock, lots or requisitions is part of the records.
        foreach (['stocks', 'lot_details', 'requisition_items'] as $table) {
            if (DB::table($table)->where('product_id', $id)->whereNull('deleted_at')->exists()) {
                $this->notify('error', __('product.manage.in_use'));

                return;
            }
        }

        try {
            app(ProductService::class)->destroy($id);
        } catch (Throwable $e) {
            Log::error('Product delete failed', ['user_id' => Auth::id(), 'exception' => $e]);
            $this->notify('error', __('product.manage.delete_failed'));

            return;
        }

        $this->deleteId = null;
        unset($this->products);
        if ($this->products->isEmpty() && $this->getPage() > 1) {
            $this->previousPage();
            unset($this->products);
        }
        $this->notify('success', __('product.manage.deleted'));
    }

    // ---- Options ----

    #[Computed]
    public function categoryOptions()
    {
        return ProductCategory::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function subCategoryOptions()
    {
        return $this->categoryId
            ? ProductSubCategory::query()->where('category_id', $this->categoryId)->orderBy('name')->get(['id', 'name'])
            : collect();
    }

    /** Managed units, plus the product's current unit if it is not in the list (old data). */
    #[Computed]
    public function unitOptions()
    {
        $names = ProductUnit::query()->orderBy('name')->pluck('name');

        if ($this->unit !== '' && ! $names->contains($this->unit)) {
            $names->push($this->unit);
        }

        return $names;
    }

    public function render()
    {
        return view('livewire.catalog.product-manager', $this->tableViewData() + ['rows' => $this->products]);
    }

    /** A new name clears the earlier similar-name warning and its confirmation. */
    public function updatedName(): void
    {
        $this->reset('similarNames', 'confirmSimilar');
    }

    public function updatedNameBn(): void
    {
        $this->reset('similarNames', 'confirmSimilar');
    }

    /**
     * Products (other than the one being edited) whose name is the same once case, spaces and
     * punctuation are ignored, and those whose name differs by only one or two letters (with the
     * same numbers, so "Patch Cord (5m)" and "(10m)" are not counted), in English or Bangla.
     *
     * @return array{0: ?string, 1: array<int, string>}  [same name, similar names]
     */
    private function lookAlikes(string $name, string $nameBn): array
    {
        $plain = fn (string $text) => preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($text));
        $letters = fn (string $text) => preg_replace('/\p{N}+/u', '', $plain($text));
        $numbers = fn (string $text) => preg_replace('/[^\p{N}]+/u', ' ', $plain($text));
        $distance = function (string $a, string $b): int {
            $x = mb_str_split($a); $y = mb_str_split($b);
            $row = range(0, count($y));
            foreach ($x as $i => $cx) {
                $prev = $row; $row = [$i + 1];
                foreach ($y as $j => $cy) {
                    $row[] = min($prev[$j + 1] + 1, $row[$j] + 1, $prev[$j] + ($cx === $cy ? 0 : 1));
                }
            }

            return end($row);
        };
        $close = fn (string $a, string $b) => $a !== '' && $b !== ''
            && $numbers($a) === $numbers($b)
            && min(mb_strlen($letters($a)), mb_strlen($letters($b))) >= 5
            && $distance($letters($a), $letters($b)) <= 2;

        $same = null;
        $similar = [];
        $others = Product::query()->when($this->productId, fn ($q) => $q->whereKeyNot($this->productId))->get(['id', 'name', 'name_bn']);
        foreach ($others as $other) {
            $label = trim($other->name . ($other->name_bn ? ' (' . $other->name_bn . ')' : ''));
            if ($plain($other->name) === $plain($name) || ($nameBn !== '' && $plain((string) $other->name_bn) === $plain($nameBn))) {
                $same ??= $label;
            } elseif ($close($other->name, $name) || ($nameBn !== '' && $close((string) $other->name_bn, $nameBn))) {
                $similar[] = $label;
            }
        }

        return [$same, array_slice($similar, 0, 5)];
    }

    private function resetForm(): void
    {
        $this->reset('productId', 'name', 'nameBn', 'description', 'categoryId', 'subCategoryId', 'unit', 'similarNames', 'confirmSimilar');
        $this->resetValidation();
    }
}
