@php $ui = \App\Livewire\Concerns\Ui::class; @endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    @include('livewire.vehicles.partials.page-top')

    <div class="content px-3">
        <div class="card card-primary">
            @include('livewire.vehicles.partials.card-head')
            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', ['id' => 'vroute', 'placeholder' => $t('search')])
                @include('livewire.vehicles.partials.loading')

                <div class="table-responsive">
                    <table class="table crud-table" id="vehicle-routes-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ $f('route_name') }}</th>
                                <th>{{ $f('start_point') }} → {{ $f('end_point') }}</th>
                                <th>{{ $f('description') }}</th>
                                <th>{{ __('vehicle.types.vehicles') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr wire:key="vroute-{{ $row->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-center crud-wrap font-weight-bold">{{ $row->name }}</td>
                                    <td class="text-center crud-wrap">{{ $row->start_point ?: __('vehicle.none') }} → {{ $row->end_point ?: __('vehicle.none') }}</td>
                                    <td class="crud-wrap">{{ \Illuminate\Support\Str::limit((string) $row->description, 80) ?: __('vehicle.none') }}</td>
                                    <td class="text-center">{{ $digits($row->vehicles_count) }}{{ app()->getLocale() === 'bn' ? 'টি' : '' }}</td>
                                    @include('livewire.vehicles.partials.row-actions', ['id' => $row->id, 'label' => $row->name])
                                </tr>
                            @empty
                                <tr wire:key="vroute-empty">
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
            <x-ui.modal wire:model="showForm" max-width="2xl" labelledby="vroute-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    @include('livewire.vehicles.partials.modal-open', ['titleId' => 'vroute-form-title'])
                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form') <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div> @enderror
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            @include('livewire.vehicles.partials.field', ['name' => 'name', 'label' => $f('route_name'), 'required' => true, 'max' => 150, 'wide' => true])
                            @include('livewire.vehicles.partials.field', ['name' => 'startPoint', 'label' => $f('start_point'), 'max' => 150])
                            @include('livewire.vehicles.partials.field', ['name' => 'endPoint', 'label' => $f('end_point'), 'max' => 150])
                            @include('livewire.vehicles.partials.field', ['name' => 'description', 'label' => $f('description'), 'type' => 'textarea', 'max' => 1000, 'wide' => true])
                        </div>
                    </div>
                    @include('livewire.vehicles.partials.modal-foot')
                </form>
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="vroute-delete"
                :title="__('vehicle.delete_title')" :text="__('vehicle.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
