@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $searching = trim($search) !== '' || $yearFilter !== '';
@endphp

{{-- ?create=1 / ?edit=ID only open a modal once; drop them so a refresh does not reopen it --}}
<div x-data
    x-init="if (/[?&](create|edit)=/.test(window.location.search)) { window.history.replaceState({}, '', window.location.pathname); }"
    x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $labels['title'] }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ $labels['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h3 class="card-title mb-0 mr-2">{{ $labels['list'] }}</h3>
                    <button type="button" class="btn btn-sm btn-success crud-btn" wire:click="openCreate"
                        title="{{ __('budget.define_new_budget') }}" aria-label="{{ __('budget.define_new_budget') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ml-1">{{ __('budget.define_new_budget') }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body position-relative">
                {{-- Toolbar: page size, fiscal-year filter and search share one row --}}
                <div class="form-row align-items-end mb-3 crud-toolbar">
                    <div class="col-5 col-md-2">
                        <label for="budget_per_page" class="mb-1">{{ __('commontable.per_page') }}</label>
                        <select id="budget_per_page" class="custom-select" wire:model.live="perPage">
                            @foreach ($perPageOptions as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-7 col-md-3">
                        <label for="budget_year_filter" class="mb-1">{{ __('budget.fiscal_year') }}</label>
                        <select id="budget_year_filter" class="custom-select" wire:model.live="yearFilter">
                            <option value="">{{ __('budget.manage.all_years') }}</option>
                            @foreach ($this->yearOptions as $option)
                                <option value="{{ $option->id }}">{{ $digits($option->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-7 mt-2 mt-md-0">
                        <label for="budget_search" class="mb-1">{{ __('commontable.search') }}</label>
                        <input type="search" id="budget_search" class="form-control" autocomplete="off"
                            wire:model.live.debounce.350ms="search"
                            placeholder="{{ $labels['search_placeholder'] }}" title="{{ $labels['search_placeholder'] }}">
                    </div>
                </div>

                <div class="crud-loading" wire:loading.flex wire:target="search, perPage, yearFilter, gotoPage, nextPage, previousPage">
                    <span class="badge badge-light p-2"><i class="fas fa-circle-notch fa-spin mr-1" aria-hidden="true"></i>{{ __('commontable.loading') }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table crud-table" id="budgets-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th class="d-none d-sm-table-cell">{{ __('budget.fiscal_year') }}</th>
                                <th>{{ $labels['owner'] }}</th>
                                <th class="d-none d-sm-table-cell">{{ __('budget.allocated_taka') }}</th>
                                <th class="d-none d-xl-table-cell">{{ __('budget.spent_taka') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('budget.remaining_taka') }}</th>
                                <th class="d-none d-xl-table-cell">{{ __('budget.remarks') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $budget)
                                @php
                                    $remaining = (float) $budget->amount - (float) $budget->spent_amount;
                                    $yearName = $digits($budget->fiscalYear->name ?? '—');
                                    $label = $ownerName($budget) . ' · ' . $yearName;
                                @endphp
                                <tr wire:key="budget-{{ $budget->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-center d-none d-sm-table-cell">
                                        <span class="badge badge-info crud-badge">{{ $yearName }}</span>
                                    </td>
                                    <td class="text-center crud-wrap">
                                        <span class="font-weight-bold">{{ $ownerName($budget) }}</span>
                                        {{-- Phones: fiscal year and the three amounts are folded in under the category --}}
                                        <small class="d-block d-sm-none text-muted">{{ $yearName }}</small>
                                        <small class="d-block d-sm-none font-weight-bold">{{ __('budget.manage.allocated_short') }}: {{ $money($budget->amount) }}</small>
                                        <small class="d-block d-sm-none text-muted">{{ __('budget.manage.spent_short') }}: {{ $money($budget->spent_amount) }}</small>
                                        <small class="d-block d-sm-none {{ $remaining < 0 ? 'text-danger' : 'text-success' }}">{{ __('budget.manage.remaining_short') }}: {{ $money($remaining) }}</small>
                                    </td>
                                    <td class="text-center text-nowrap d-none d-sm-table-cell">
                                        <span class="font-weight-bold">{{ $money($budget->amount) }}</span>
                                        {{-- Narrower screens: spent and remaining are folded in under the allocation --}}
                                        <small class="d-block d-xl-none text-muted">{{ __('budget.manage.spent_short') }}: {{ $money($budget->spent_amount) }}</small>
                                        <small class="d-block d-md-none {{ $remaining < 0 ? 'text-danger' : 'text-success' }}">{{ __('budget.manage.remaining_short') }}: {{ $money($remaining) }}</small>
                                    </td>
                                    <td class="text-center text-nowrap text-muted d-none d-xl-table-cell">{{ $money($budget->spent_amount) }}</td>
                                    <td class="text-center text-nowrap font-weight-bold d-none d-md-table-cell {{ $remaining < 0 ? 'text-danger' : 'text-success' }}">{{ $money($remaining) }}</td>
                                    <td class="text-center crud-wrap d-none d-xl-table-cell">{{ $budget->remarks ?: '—' }}</td>
                                    <td class="crud-actions">
                                        <button type="button" class="btn btn-sm btn-primary crud-btn" wire:click="openEdit({{ $budget->id }})"
                                            data-tip="{{ __('buttons.edit') }}" aria-label="{{ __('buttons.edit') }}: {{ $label }}">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger crud-btn" wire:click="confirmDelete({{ $budget->id }})"
                                            data-tip="{{ __('buttons.delete') }}" aria-label="{{ __('buttons.delete') }}: {{ $label }}">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr wire:key="budget-empty">
                                    <td colspan="8" class="crud-empty">{{ $searching ? __('commontable.dt_zero_records') : __('commontable.no_record_found') }}</td>
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
            {{-- Create / edit --}}
            <x-ui.modal wire:model="showForm" max-width="2xl" labelledby="budget-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="budget-form-title" class="{{ $ui::MODAL_TITLE }}">
                                {{ $budgetId ? __('budget.edit_budget_allocation') : __('budget.define_new_budget') }}
                            </h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ $budgetId ? $labels['edit_hint'] : $labels['hint'] }}</p>
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

                        {{-- Category and fiscal year share one row (stacked on phones) --}}
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            <div class="tw-min-w-0">
                                <label for="budget-category" class="{{ $ui::LABEL }}">{{ $labels['owner'] }} @unless ($budgetId)<span class="tw-text-rose-600">*</span>@endunless</label>
                                @if ($budgetId)
                                    <input id="budget-category" type="text" class="{{ $ui::INPUT }}" value="{{ $categoryLabel }}" disabled>
                                @else
                                    <x-ui.search-select id="budget-category" wire:model="categoryId" :visible="5"
                                        :options="$this->ownerOptions->map(fn ($option) => ['value' => $option->id, 'label' => $option->name])"
                                        :placeholder="$labels['select_owner']" :search-placeholder="$labels['search_owner']"
                                        :invalid="$errors->has('categoryId')" />
                                @endif
                                @error('categoryId') <p id="budget-category-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                            <div class="tw-min-w-0">
                                <label for="budget-year" class="{{ $ui::LABEL }}">{{ __('budget.fiscal_year') }} @unless ($budgetId)<span class="tw-text-rose-600">*</span>@endunless</label>
                                @if ($budgetId)
                                    <input id="budget-year" type="text" class="{{ $ui::INPUT }}" value="{{ $digits($fiscalYearLabel) }}" disabled>
                                @else
                                    <x-ui.search-select id="budget-year" wire:model="fiscalYearId" :visible="5"
                                        :options="$this->activeYearOptions->map(fn ($option) => ['value' => $option->id, 'label' => $digits($option->name)])"
                                        :placeholder="__('budget.manage.select_year')" :search-placeholder="__('budget.manage.search_year')"
                                        :invalid="$errors->has('fiscalYearId')" />
                                @endif
                                @error('fiscalYearId') <p id="budget-year-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="budget-amount" class="{{ $ui::LABEL }}">{{ __('budget.manage.amount') }} <span class="tw-text-rose-600">*</span></label>
                            <input id="budget-amount" type="text" inputmode="decimal" maxlength="20" wire:model="amount" autocomplete="off"
                                placeholder="{{ $digits('0.00') }}"
                                class="{{ $ui::INPUT }} @error('amount') {{ $ui::INPUT_ERROR }} @enderror"
                                aria-describedby="budget-amount-hint @error('amount') budget-amount-error @enderror"
                                @error('amount') aria-invalid="true" @enderror>
                            @error('amount')
                                <p id="budget-amount-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p>
                            @enderror
                            <p id="budget-amount-hint" class="{{ $ui::HINT }}">
                                @if ($budgetId)
                                    {{ __('budget.manage.spent_hint', ['spent' => $money($spentAmount)]) }}
                                @else
                                    {{ __('budget.manage.amount_hint') }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <label for="budget-remarks" class="{{ $ui::LABEL }}">{{ __('budget.remarks') }}</label>
                            <textarea id="budget-remarks" rows="2" maxlength="1000" wire:model="remarks"
                                class="{{ $ui::INPUT }} tw-resize-y @error('remarks') {{ $ui::INPUT_ERROR }} @enderror"></textarea>
                            @error('remarks') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="{{ $ui::BTN_PRIMARY }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save"></i>
                            <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="save"></i>
                            {{ $budgetId ? __('buttons.update') : __('buttons.save') }}
                        </button>
                    </div>
                </form>
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="budget-delete"
                :title="__('budget.manage.delete_title')" :text="__('budget.manage.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
