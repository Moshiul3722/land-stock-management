@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $searching = trim($search) !== '' || $categoryFilter !== '';
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('product.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('product.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h3 class="card-title mb-0 mr-2">{{ __('product.product_list') }}</h3>
                    <button type="button" class="btn btn-sm btn-success crud-btn" wire:click="openCreate"
                        title="{{ __('product.manage.add') }}" aria-label="{{ __('product.manage.add') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ml-1">{{ __('product.manage.add') }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body position-relative">
                {{-- Toolbar: page size, category filter and search share one row --}}
                <div class="form-row align-items-end mb-3 crud-toolbar">
                    <div class="col-5 col-md-2">
                        <label for="product_per_page" class="mb-1">{{ __('commontable.per_page') }}</label>
                        <select id="product_per_page" class="custom-select" wire:model.live="perPage">
                            @foreach ($perPageOptions as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7 col-md-4">
                        <label for="product_category_filter" class="mb-1">{{ __('product.manage.category') }}</label>
                        <select id="product_category_filter" class="custom-select" wire:model.live="categoryFilter">
                            <option value="">{{ __('product.manage.all_categories') }}</option>
                            @foreach ($this->categoryOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mt-2 mt-md-0">
                        <label for="product_search" class="mb-1">{{ __('commontable.search') }}</label>
                        <input type="search" id="product_search" class="form-control" autocomplete="off"
                            wire:model.live.debounce.350ms="search"
                            placeholder="{{ __('product.manage.search_placeholder') }}" title="{{ __('product.manage.search_placeholder') }}">
                    </div>
                </div>

                <div class="crud-loading" wire:loading.flex wire:target="search, perPage, categoryFilter, gotoPage, nextPage, previousPage">
                    <span class="badge badge-light p-2"><i class="fas fa-circle-notch fa-spin mr-1" aria-hidden="true"></i>{{ __('commontable.loading') }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table crud-table" id="products-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ __('product.product_name') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('product.manage.category') }}</th>
                                <th class="d-none d-lg-table-cell">{{ __('product.manage.sub_category') }}</th>
                                <th class="d-none d-sm-table-cell">{{ __('product.manage.unit') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $product)
                                <tr wire:key="product-{{ $product->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-left crud-wrap">
                                        {{-- English name, with the Bangla name in brackets beside it --}}
                                        <span class="font-weight-bold">{{ $product->name . (trim((string) $product->name_bn) !== '' ? ' (' . $product->name_bn . ')' : '') }}</span>
                                        @if ($product->description)
                                            <small class="d-block text-muted">{{ $product->description }}</small>
                                        @endif
                                        {{-- Narrow screens: hidden columns are summarised under the name --}}
                                        <small class="d-block d-md-none text-muted">
                                            {{ collect([$product->category->name ?? null, $product->subCategory->name ?? null])->filter()->join(' › ') }}
                                        </small>
                                        <small class="d-block d-sm-none text-muted">{{ __('product.manage.unit') }}: {{ $product->unit_label ?: '—' }}</small>
                                    </td>
                                    <td class="text-center crud-wrap d-none d-md-table-cell">{{ $product->category->name ?? '—' }}</td>
                                    <td class="text-center crud-wrap d-none d-lg-table-cell">{{ $product->subCategory->name ?? '—' }}</td>
                                    <td class="text-center d-none d-sm-table-cell">{{ $product->unit_label ?: '—' }}</td>
                                    <td class="crud-actions">
                                        <button type="button" class="btn btn-sm btn-primary crud-btn" wire:click="openEdit({{ $product->id }})"
                                            data-tip="{{ __('buttons.edit') }}" aria-label="{{ __('buttons.edit') }}: {{ $product->name }}">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger crud-btn" wire:click="confirmDelete({{ $product->id }})"
                                            data-tip="{{ __('buttons.delete') }}" aria-label="{{ __('buttons.delete') }}: {{ $product->name }}">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr wire:key="product-empty">
                                    <td colspan="6" class="crud-empty">{{ $searching ? __('commontable.dt_zero_records') : __('commontable.no_record_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.partials.table-footer', ['paginator' => $rows])
            </div>
        </div>
    </div>

    <div class="tw-root">
        <div class="tw-font-sans tw-text-slate-800">
            <x-ui.modal wire:model="showForm" max-width="3xl" labelledby="product-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="product-form-title" class="{{ $ui::MODAL_TITLE }}">
                                {{ $productId ? __('product.edit_product_title') : __('product.add_product_title') }}
                            </h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ __('product.manage.hint') }}</p>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form')
                            <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div>
                        @enderror

                        {{-- The product's name in both languages --}}
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            <div>
                                <label for="product-name" class="{{ $ui::LABEL }}">{{ __('product.manage.name_en') }} <span class="tw-text-rose-600">*</span></label>
                                <input id="product-name" type="text" maxlength="191" wire:model="name" autocomplete="off" autofocus
                                    class="{{ $ui::INPUT }} @error('name') {{ $ui::INPUT_ERROR }} @enderror"
                                    @error('name') aria-invalid="true" aria-describedby="product-name-error" @enderror>
                                @error('name') <p id="product-name-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="product-name-bn" class="{{ $ui::LABEL }}">{{ __('product.manage.name_bn') }}</label>
                                <input id="product-name-bn" type="text" maxlength="191" wire:model="nameBn" autocomplete="off" lang="bn"
                                    class="{{ $ui::INPUT }} @error('nameBn') {{ $ui::INPUT_ERROR }} @enderror"
                                    @error('nameBn') aria-invalid="true" aria-describedby="product-name-bn-error" @enderror>
                                @error('nameBn') <p id="product-name-bn-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Category, sub-category and unit share one row (stacked on phones) --}}
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-3">
                            <div class="tw-min-w-0">
                                <label for="product-category" class="{{ $ui::LABEL }}">{{ __('product.manage.category') }} <span class="tw-text-rose-600">*</span></label>
                                <x-ui.search-select id="product-category" wire:model.live="categoryId" :visible="5"
                                    :options="$this->categoryOptions->map(fn ($option) => ['value' => $option->id, 'label' => $option->name])"
                                    :placeholder="__('product.manage.select_category')" :search-placeholder="__('product.manage.search_category')"
                                    :invalid="$errors->has('categoryId')" />
                                @error('categoryId') <p id="product-category-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                            <div class="tw-min-w-0">
                                <label for="product-sub-category" class="{{ $ui::LABEL }}">{{ __('product.manage.sub_category') }}</label>
                                <x-ui.search-select id="product-sub-category" wire:model="subCategoryId" :disabled="! $categoryId" :visible="5"
                                    :options="$this->subCategoryOptions->map(fn ($option) => ['value' => $option->id, 'label' => $option->name])"
                                    :placeholder="$categoryId ? __('product.manage.select_sub_category') : __('product.manage.select_category_first')"
                                    :search-placeholder="__('product.manage.search_sub_category')"
                                    :invalid="$errors->has('subCategoryId')" />
                                @error('subCategoryId') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                            </div>
                            <div class="tw-min-w-0">
                                <label for="product-unit" class="{{ $ui::LABEL }}">{{ __('product.manage.unit') }} <span class="tw-text-rose-600">*</span></label>
                                <x-ui.search-select id="product-unit" wire:model="unit" :visible="5"
                                    :options="$this->unitOptions->map(fn ($option) => ['value' => $option, 'label' => \App\Models\ProductUnit::label($option)])"
                                    :placeholder="__('product.manage.select_unit')" :search-placeholder="__('product.manage.search_unit')"
                                    :invalid="$errors->has('unit')" />
                                @error('unit') <p id="product-unit-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="product-description" class="{{ $ui::LABEL }}">{{ __('product.description') }}</label>
                            <textarea id="product-description" rows="3" maxlength="1000" wire:model="description"
                                class="{{ $ui::INPUT }} tw-resize-y @error('description') {{ $ui::INPUT_ERROR }} @enderror"></textarea>
                            @error('description') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="{{ $ui::BTN_PRIMARY }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save"></i>
                            <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="save"></i>
                            {{ $productId ? __('buttons.update') : __('buttons.save') }}
                        </button>
                    </div>
                </form>
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="product-delete"
                :title="__('product.manage.delete_title')" :text="__('product.manage.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
