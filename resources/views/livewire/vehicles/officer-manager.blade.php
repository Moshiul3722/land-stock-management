@php $ui = \App\Livewire\Concerns\Ui::class; @endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    @include('livewire.vehicles.partials.page-top')

    <div class="content px-3">
        <div class="card card-primary">
            @include('livewire.vehicles.partials.card-head')
            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', ['id' => 'vofficer', 'placeholder' => $t('search')])
                @include('livewire.vehicles.partials.loading')

                <div class="table-responsive">
                    <table class="table crud-table" id="vehicle-officers-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ $f('name_bn') }}</th>
                                <th>{{ $f('designation') }}</th>
                                <th>{{ $f('department') }}</th>
                                <th>{{ $f('mobile') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr wire:key="vofficer-{{ $row->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="crud-wrap">
                                        <span class="font-weight-bold">{{ $row->name_bn }}</span>
                                        @if ($row->name && $row->name !== $row->name_bn)<br><small class="text-muted">{{ $row->name }}</small>@endif
                                    </td>
                                    <td class="text-center crud-wrap">{{ $row->designation ?: __('vehicle.none') }}</td>
                                    <td class="text-center crud-wrap">{{ $row->department ? (app()->getLocale() === 'bn' && $row->department->name_bn ? $row->department->name_bn : $row->department->name) : __('vehicle.none') }}</td>
                                    <td class="text-center">{{ $row->mobile ? $digits($row->mobile) : __('vehicle.none') }}</td>
                                    @include('livewire.vehicles.partials.row-actions', ['id' => $row->id, 'label' => $row->name_bn])
                                </tr>
                            @empty
                                <tr wire:key="vofficer-empty">
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
            <x-ui.modal wire:model="showForm" max-width="2xl" labelledby="vofficer-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    @include('livewire.vehicles.partials.modal-open', ['titleId' => 'vofficer-form-title'])
                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form') <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div> @enderror
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            @include('livewire.vehicles.partials.field', ['name' => 'nameBn', 'label' => $f('name_bn'), 'required' => true, 'max' => 150, 'lang' => 'bn'])
                            @include('livewire.vehicles.partials.field', ['name' => 'name', 'label' => $f('name_en'), 'max' => 150])
                            @include('livewire.vehicles.partials.field', ['name' => 'designation', 'label' => $f('designation'), 'max' => 150])
                            @include('livewire.vehicles.partials.search-select', ['name' => 'departmentId', 'label' => $f('department'), 'options' => $this->departments])
                            @include('livewire.vehicles.partials.field', ['name' => 'mobile', 'label' => $f('mobile'), 'type' => 'tel', 'max' => 17])
                        </div>
                    </div>
                    @include('livewire.vehicles.partials.modal-foot')
                </form>
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="vofficer-delete"
                :title="__('vehicle.delete_title')" :text="__('vehicle.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
