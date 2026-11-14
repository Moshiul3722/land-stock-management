@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $searching = trim($search) !== '';
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('category.manage.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('category.manage.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h3 class="card-title mb-0 mr-2">{{ __('category.table_header') }}</h3>
                    <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                        <button type="button" class="btn btn-sm btn-light crud-btn" wire:click="openSubCreate"
                            title="{{ __('category.manage.add_sub') }}" aria-label="{{ __('category.manage.add_sub') }}">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            <span class="d-none d-md-inline ml-1">{{ __('category.manage.add_sub') }}</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-success crud-btn" wire:click="openCategoryCreate"
                            title="{{ __('category.manage.add_category') }}" aria-label="{{ __('category.manage.add_category') }}">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            <span class="d-none d-sm-inline ml-1">{{ __('category.manage.add_category') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', [
                    'id' => 'category',
                    'placeholder' => __('category.manage.search_placeholder'),
                ])

                <div class="crud-loading" wire:loading.flex wire:target="search, perPage, gotoPage, nextPage, previousPage, gotoSubPage">
                    <span class="badge badge-light p-2"><i class="fas fa-circle-notch fa-spin mr-1" aria-hidden="true"></i>{{ __('commontable.loading') }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table crud-table" id="categories-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ __('category.table_name') }}</th>
                                <th class="d-none d-sm-table-cell">{{ __('category.manage.sub_categories') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('category.manage.products') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $category)
                                @php
                                    $open = in_array($category->id, $expanded, true);
                                    $serial = $rows->firstItem() + $loop->index;
                                @endphp
                                {{-- One row per category, keyed by its id --}}
                                <tr wire:key="category-{{ $category->id }}">
                                    <td class="crud-serial">{{ $digits($serial) }}</td>
                                    <td class="text-left crud-wrap">
                                        <span class="font-weight-bold">{{ $category->name }}</span>
                                        @if ($category->description)
                                            <small class="d-block text-muted">{{ $category->description }}</small>
                                        @endif
                                        {{-- Phones: the sub-category column is folded in under the name --}}
                                        <div class="d-sm-none mt-1 crud-toggle-inline">
                                            @if ($category->sub_categories_count > 0)
                                                <button type="button" class="crud-toggle" wire:click="toggle({{ $category->id }})"
                                                    aria-expanded="{{ $open ? 'true' : 'false' }}"
                                                    aria-controls="category-subs-{{ $category->id }}">
                                                    <i class="fas fa-chevron-{{ $open ? 'down' : 'right' }} mr-1" aria-hidden="true"></i>
                                                    {{ __('category.manage.sub_count', ['count' => $digits($category->sub_categories_count)]) }}
                                                </button>
                                            @else
                                                <span class="text-muted">{{ __('category.manage.no_subs') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center d-none d-sm-table-cell">
                                        @if ($category->sub_categories_count > 0)
                                            <button type="button" class="crud-toggle" wire:click="toggle({{ $category->id }})"
                                                aria-expanded="{{ $open ? 'true' : 'false' }}"
                                                aria-controls="category-subs-{{ $category->id }}">
                                                <i class="fas fa-chevron-{{ $open ? 'down' : 'right' }} mr-1" aria-hidden="true"></i>
                                                {{ __('category.manage.sub_count', ['count' => $digits($category->sub_categories_count)]) }}
                                            </button>
                                        @else
                                            <span class="text-muted">{{ __('category.manage.no_subs') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">{{ $digits($category->products_count) }}</td>
                                    <td class="crud-actions">
                                        <button type="button" class="btn btn-sm btn-success crud-btn"
                                            wire:click="openSubCreate({{ $category->id }})"
                                            data-tip="{{ __('category.manage.add_sub') }}"
                                            aria-label="{{ __('category.manage.add_sub') }}: {{ $category->name }}">
                                            <i class="fas fa-plus" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary crud-btn"
                                            wire:click="openCategoryEdit({{ $category->id }})"
                                            data-tip="{{ __('buttons.edit') }}"
                                            aria-label="{{ __('buttons.edit') }}: {{ $category->name }}">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger crud-btn"
                                            wire:click="confirmDelete('category', {{ $category->id }})"
                                            data-tip="{{ __('buttons.delete') }}"
                                            aria-label="{{ __('buttons.delete') }}: {{ $category->name }}">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Its sub-categories, nested under the category --}}
                                @if ($open && $category->subCategories->isNotEmpty())
                                    @php
                                        $subTotal = $category->subCategories->count();
                                        $subLast = (int) ceil($subTotal / $subsPerPage);
                                        $subCurrent = min(max(1, $subPage), $subLast);
                                        $subItems = $category->subCategories->forPage($subCurrent, $subsPerPage);
                                        $subStart = ($subCurrent - 1) * $subsPerPage + 1;
                                    @endphp
                                    <tr wire:key="category-subs-{{ $category->id }}" class="crud-nested" id="category-subs-{{ $category->id }}">
                                        <td class="d-none d-sm-table-cell"></td>
                                        <td colspan="4">
                                            <ul class="crud-nested-list" aria-label="{{ __('category.manage.sub_categories') }}: {{ $category->name }}">
                                                @foreach ($subItems as $sub)
                                                    <li wire:key="sub-{{ $sub->id }}">
                                                        <span class="crud-wrap text-left">
                                                            <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-2" aria-hidden="true"></i>
                                                            <button type="button" class="crud-sub-link" wire:click="showSubProducts({{ $sub->id }})"
                                                                data-tip="{{ __('category.manage.view_products') }}"
                                                                aria-label="{{ __('category.manage.view_products') }}: {{ $sub->name }}">
                                                                <span class="crud-sub-name">{{ $sub->name }}</span>
                                                                @if ($sub->code)
                                                                    <span class="badge badge-light border ml-1">{{ $sub->code }}</span>
                                                                @endif
                                                                <small class="text-muted ml-1 text-nowrap">({{ __('category.manage.product_count', ['count' => $digits($sub->products_count)]) }})</small>
                                                            </button>
                                                        </span>
                                                        <span class="text-nowrap">
                                                            <button type="button" class="btn btn-sm btn-outline-primary crud-btn"
                                                                wire:click="openSubEdit({{ $sub->id }})"
                                                                data-tip="{{ __('buttons.edit') }}"
                                                                aria-label="{{ __('buttons.edit') }}: {{ $sub->name }}">
                                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger crud-btn"
                                                                wire:click="confirmDelete('sub', {{ $sub->id }})"
                                                                data-tip="{{ __('buttons.delete') }}"
                                                                aria-label="{{ __('buttons.delete') }}: {{ $sub->name }}">
                                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                            </button>
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if ($subLast > 1)
                                                <div class="crud-footer crud-sub-footer">
                                                    <div class="crud-info" aria-live="polite">
                                                        {{ strtr(__('commontable.dt_info'), [
                                                            '_TOTAL_' => $digits($subTotal),
                                                            '_START_' => $digits($subStart),
                                                            '_END_' => $digits($subStart + $subItems->count() - 1),
                                                        ]) }}
                                                    </div>
                                                    <nav aria-label="{{ __('commontable.pagination') }}: {{ $category->name }}">
                                                        <ul class="pagination">
                                                            <li class="page-item {{ $subCurrent === 1 ? 'disabled' : '' }}">
                                                                <button type="button" class="page-link" wire:click="gotoSubPage({{ $subCurrent - 1 }})"
                                                                    @disabled($subCurrent === 1) aria-label="{{ __('commontable.dt_previous') }}">‹</button>
                                                            </li>
                                                            @foreach (range(max(1, $subCurrent - 2), min($subLast, $subCurrent + 2)) as $page)
                                                                <li class="page-item {{ $page === $subCurrent ? 'active' : '' }}" wire:key="sub-page-{{ $category->id }}-{{ $page }}">
                                                                    <button type="button" class="page-link" wire:click="gotoSubPage({{ $page }})"
                                                                        @if ($page === $subCurrent) aria-current="page" @endif>{{ $digits($page) }}</button>
                                                                </li>
                                                            @endforeach
                                                            <li class="page-item {{ $subCurrent === $subLast ? 'disabled' : '' }}">
                                                                <button type="button" class="page-link" wire:click="gotoSubPage({{ $subCurrent + 1 }})"
                                                                    @disabled($subCurrent === $subLast) aria-label="{{ __('commontable.dt_next') }}">›</button>
                                                            </li>
                                                        </ul>
                                                    </nav>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr wire:key="category-empty">
                                    <td colspan="5" class="crud-empty">
                                        {{ $searching ? __('commontable.dt_zero_records') : __('commontable.no_record_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.partials.table-footer', ['paginator' => $rows])
            </div>
        </div>
    </div>

    {{-- Modals use the same Tailwind dialog as the requisition form --}}
    <div class="tw-root">
        <div class="tw-font-sans tw-text-slate-800">

            {{-- Category create / edit --}}
            <x-ui.modal wire:model="showCategoryForm" max-width="2xl" labelledby="category-form-title">
                <form wire:submit="saveCategory" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="category-form-title" class="{{ $ui::MODAL_TITLE }}">
                                {{ $categoryId ? __('category.title_edit') : __('category.title_create') }}
                            </h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ __('category.manage.category_hint') }}</p>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('categoryForm')
                            <div class="{{ $ui::FORM_ALERT }}" role="alert">
                                <i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span>
                            </div>
                        @enderror

                        <div>
                            <label for="category-name" class="{{ $ui::LABEL }}">{{ __('category.manage.category_name') }} <span class="tw-text-rose-600">*</span></label>
                            <input id="category-name" type="text" maxlength="191" wire:model="categoryName" autocomplete="off" autofocus
                                class="{{ $ui::INPUT }} @error('categoryName') {{ $ui::INPUT_ERROR }} @enderror"
                                @error('categoryName') aria-invalid="true" aria-describedby="category-name-error" @enderror>
                            @error('categoryName') <p id="category-name-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="category-description" class="{{ $ui::LABEL }}">{{ __('category.manage.description') }}</label>
                            <textarea id="category-description" rows="3" maxlength="1000" wire:model="categoryDescription"
                                class="{{ $ui::INPUT }} tw-resize-y @error('categoryDescription') {{ $ui::INPUT_ERROR }} @enderror"></textarea>
                            @error('categoryDescription') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveCategory" class="{{ $ui::BTN_PRIMARY }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="saveCategory"></i>
                            <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="saveCategory"></i>
                            {{ $categoryId ? __('buttons.update') : __('buttons.save') }}
                        </button>
                    </div>
                </form>
            </x-ui.modal>

            {{-- Sub-category create / edit --}}
            <x-ui.modal wire:model="showSubForm" max-width="2xl" labelledby="sub-form-title">
                <form wire:submit="saveSub" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="sub-form-title" class="{{ $ui::MODAL_TITLE }}">
                                {{ $subId ? __('category.manage.edit_sub') : __('category.manage.add_sub') }}
                            </h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ __('category.manage.sub_hint') }}</p>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('subForm')
                            <div class="{{ $ui::FORM_ALERT }}" role="alert">
                                <i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span>
                            </div>
                        @enderror

                        <div>
                            <label for="sub-parent" class="{{ $ui::LABEL }}">{{ __('category.manage.category') }} <span class="tw-text-rose-600">*</span></label>
                            <x-ui.search-select id="sub-parent" wire:model="subParentId"
                                :options="$this->categoryOptions->map(fn ($option) => ['value' => $option->id, 'label' => $option->name])"
                                :placeholder="__('category.manage.select_category')" :search-placeholder="__('category.manage.search_category')"
                                :invalid="$errors->has('subParentId')" />
                            @error('subParentId') <p id="sub-parent-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                        </div>

                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-[1.6fr_1fr]">
                            <div>
                                <label for="sub-name" class="{{ $ui::LABEL }}">{{ __('category.manage.sub_name') }} <span class="tw-text-rose-600">*</span></label>
                                <input id="sub-name" type="text" maxlength="191" wire:model="subName" autocomplete="off" autofocus
                                    class="{{ $ui::INPUT }} @error('subName') {{ $ui::INPUT_ERROR }} @enderror"
                                    @error('subName') aria-invalid="true" aria-describedby="sub-name-error" @enderror>
                                @error('subName') <p id="sub-name-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="sub-code" class="{{ $ui::LABEL }}">{{ __('category.manage.code') }}</label>
                                <input id="sub-code" type="text" value="{{ $subCode }}" readonly tabindex="-1"
                                    aria-describedby="sub-code-hint"
                                    class="{{ $ui::INPUT }} tw-cursor-default tw-bg-slate-100 tw-font-semibold tw-tabular-nums tw-text-slate-700">
                                <p id="sub-code-hint" class="{{ $ui::HINT }}">{{ __('category.manage.code_auto') }}</p>
                                @error('subCode') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveSub" class="{{ $ui::BTN_PRIMARY }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="saveSub"></i>
                            <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="saveSub"></i>
                            {{ $subId ? __('buttons.update') : __('buttons.save') }}
                        </button>
                    </div>
                </form>
            </x-ui.modal>

            {{-- Products of one sub-category --}}
            <x-ui.modal wire:model="showProducts" max-width="3xl" labelledby="sub-products-title">
                <div class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col">
                    <div class="{{ $ui::MODAL_HEADER }} crud-head-wrap">
                        <div class="tw-min-w-0 tw-flex-1 crud-head-title">
                            <h2 id="sub-products-title" class="{{ $ui::MODAL_TITLE }}">
                                {{ $productsSub?->name }}
                                @if ($productsSub?->code)
                                    <span class="badge badge-light border ml-1 align-middle">{{ $productsSub->code }}</span>
                                @endif
                            </h2>
                            <p class="{{ $ui::MODAL_HINT }}">
                                {{ __('category.manage.products_hint', ['category' => $productsSub?->category?->name ?? '—']) }}
                            </p>
                        </div>
                        <div class="crud-head-search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input type="search" id="sub-products-search" autocomplete="off"
                                wire:model.live.debounce.300ms="productsSearch"
                                placeholder="{{ __('category.manage.search_products') }}"
                                title="{{ __('category.manage.search_products_hint') }}"
                                aria-label="{{ __('category.manage.search_products_hint') }}">
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }} crud-head-close" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="{{ $ui::MODAL_BODY }}">
                        <div wire:loading.class="crud-busy" wire:target="gotoProductsPage, productsSearch">
                        @if ($subProducts && $subProducts->total() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover crud-table crud-modal-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                            <th class="text-left">{{ __('product.product_name') }}</th>
                                            <th>{{ __('product.manage.unit') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subProducts as $product)
                                            <tr wire:key="sub-product-{{ $product->id }}">
                                                <td class="crud-serial">{{ $digits($subProducts->firstItem() + $loop->index) }}</td>
                                                <td class="text-left crud-wrap">
                                                    {{ $product->name }}
                                                    @if ($product->description)
                                                        <small class="d-block text-muted">{{ $product->description }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $product->unit_label ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="crud-footer crud-sub-footer">
                                <div class="crud-info" aria-live="polite">
                                    {{ strtr(__('commontable.dt_info'), [
                                        '_TOTAL_' => $digits($subProducts->total()),
                                        '_START_' => $digits($subProducts->firstItem()),
                                        '_END_' => $digits($subProducts->lastItem()),
                                    ]) }}
                                </div>
                                @if ($subProducts->lastPage() > 1)
                                    @php $pCurrent = $subProducts->currentPage(); $pLast = $subProducts->lastPage(); @endphp
                                    <nav aria-label="{{ __('commontable.pagination') }}">
                                        <ul class="pagination">
                                            <li class="page-item {{ $pCurrent === 1 ? 'disabled' : '' }}">
                                                <button type="button" class="page-link" wire:click="gotoProductsPage({{ $pCurrent - 1 }})"
                                                    @disabled($pCurrent === 1) aria-label="{{ __('commontable.dt_previous') }}">‹</button>
                                            </li>
                                            @foreach (range(max(1, $pCurrent - 2), min($pLast, $pCurrent + 2)) as $page)
                                                <li class="page-item {{ $page === $pCurrent ? 'active' : '' }}" wire:key="products-page-{{ $page }}">
                                                    <button type="button" class="page-link" wire:click="gotoProductsPage({{ $page }})"
                                                        @if ($page === $pCurrent) aria-current="page" @endif>{{ $digits($page) }}</button>
                                                </li>
                                            @endforeach
                                            <li class="page-item {{ $pCurrent === $pLast ? 'disabled' : '' }}">
                                                <button type="button" class="page-link" wire:click="gotoProductsPage({{ $pCurrent + 1 }})"
                                                    @disabled($pCurrent === $pLast) aria-label="{{ __('commontable.dt_next') }}">›</button>
                                            </li>
                                        </ul>
                                    </nav>
                                @endif
                            </div>
                        @else
                            <p class="crud-empty mb-0">
                                {{ trim($productsSearch) !== '' ? __('commontable.dt_zero_records') : __('category.manage.no_products') }}
                            </p>
                        @endif
                        </div>
                    </div>

                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('commontable.close') }}</button>
                    </div>
                </div>
            </x-ui.modal>

            {{-- Delete confirmation --}}
            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="category-delete"
                :title="$deleteType === 'sub' ? __('category.manage.delete_sub_title') : __('category.manage.delete_category_title')"
                :text="$deleteType === 'sub' ? __('category.manage.delete_sub_text') : __('category.manage.delete_category_text')"
                :name="$deleteLabel" />
        </div>
    </div>
</div>
