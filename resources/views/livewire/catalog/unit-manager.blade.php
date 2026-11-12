@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $searching = trim($search) !== '';
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('unit.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('unit.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h3 class="card-title mb-0 mr-2">{{ __('unit.table_header') }}</h3>
                    <button type="button" class="btn btn-sm btn-success crud-btn" wire:click="openCreate"
                        title="{{ __('unit.add') }}" aria-label="{{ __('unit.add') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline ml-1">{{ __('unit.add') }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', ['id' => 'unit', 'placeholder' => __('unit.search_placeholder')])

                <div class="crud-loading" wire:loading.flex wire:target="search, perPage, gotoPage, nextPage, previousPage">
                    <span class="badge badge-light p-2"><i class="fas fa-circle-notch fa-spin mr-1" aria-hidden="true"></i>{{ __('commontable.loading') }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table crud-table" id="units-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ __('unit.name') }}</th>
                                <th>{{ __('unit.products') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $unit)
                                <tr wire:key="unit-{{ $unit->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-left crud-wrap font-weight-bold">{{ $unit->name }}</td>
                                    <td class="text-center">{{ $digits($unit->products_count) }}</td>
                                    <td class="crud-actions">
                                        <button type="button" class="btn btn-sm btn-primary crud-btn" wire:click="openEdit({{ $unit->id }})"
                                            title="{{ __('buttons.edit') }}" aria-label="{{ __('buttons.edit') }}: {{ $unit->name }}">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger crud-btn" wire:click="confirmDelete({{ $unit->id }})"
                                            title="{{ __('buttons.delete') }}" aria-label="{{ __('buttons.delete') }}: {{ $unit->name }}">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr wire:key="unit-empty">
                                    <td colspan="4" class="crud-empty">{{ $searching ? __('commontable.dt_zero_records') : __('commontable.no_record_found') }}</td>
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
            <x-ui.modal wire:model="showForm" max-width="lg" labelledby="unit-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0">
                            <h2 id="unit-form-title" class="{{ $ui::MODAL_TITLE }}">{{ $unitId ? __('unit.edit') : __('unit.add') }}</h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ __('unit.hint') }}</p>
                        </div>
                        <button type="button" x-on:click="open = false" class="{{ $ui::ICON_BTN }} tw--mr-2 tw--mt-1" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form')
                            <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div>
                        @enderror
                        <div>
                            <label for="unit-name" class="{{ $ui::LABEL }}">{{ __('unit.name') }} <span class="tw-text-rose-600">*</span></label>
                            <input id="unit-name" type="text" maxlength="50" wire:model="name" autocomplete="off" autofocus
                                class="{{ $ui::INPUT }} @error('name') {{ $ui::INPUT_ERROR }} @enderror"
                                @error('name') aria-invalid="true" aria-describedby="unit-name-error" @enderror>
                            @error('name') <p id="unit-name-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="{{ $ui::BTN_PRIMARY }}">
                            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" wire:loading wire:target="save"></i>
                            <i class="fas fa-save" aria-hidden="true" wire:loading.remove wire:target="save"></i>
                            {{ $unitId ? __('buttons.update') : __('buttons.save') }}
                        </button>
                    </div>
                </form>
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="unit-delete"
                :title="__('unit.delete_title')" :text="__('unit.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
