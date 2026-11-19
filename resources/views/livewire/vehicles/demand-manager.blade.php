@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $isBn = app()->getLocale() === 'bn';
    $dateText = fn ($date) => $date ? $digits($date->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat($isBn ? 'j F Y' : 'd M Y')) : __('vehicle.none');
    $typeTone = ['fuel' => 'badge-warning', 'repair' => 'badge-danger', 'parts' => 'badge-info', 'other' => 'badge-secondary'];
    $typeOptions = collect(\App\Models\VehicleDemand::TYPES)->mapWithKeys(fn ($type) => [$type => __('vehicle.demands.types.' . $type)])->all();
    $showDriver = ! $this->isDriver;
    $amount = function ($row) use ($digits) {
        if ($row->quantity === null) {
            return __('vehicle.none');
        }
        $number = rtrim(rtrim((string) $row->quantity, '0'), '.');

        return trim($digits($number) . ' ' . $row->unit);
    };
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    @include('livewire.vehicles.partials.page-top')

    <div class="content px-3">
        @if ($this->isDriver && ! $this->me)
            <div class="alert alert-warning">{{ $t('not_driver') }}</div>
        @elseif (! $this->canAdd)
            <div class="alert alert-info">{{ $t('no_vehicle') }}</div>
        @endif

        <div class="card card-primary">
            @include('livewire.vehicles.partials.card-head', ['canAdd' => $this->canAdd])
            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', ['id' => 'vdemand', 'placeholder' => $t('search')])
                @include('livewire.vehicles.partials.loading')

                <div class="table-responsive">
                    <table class="table crud-table" id="vehicle-demands-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ $f('demand_date') }}</th>
                                <th>{{ $f('vehicle') }}</th>
                                @if ($showDriver)<th>{{ $f('driver') }}</th>@endif
                                <th>{{ $f('demand_type') }}</th>
                                <th>{{ $f('quantity') }}</th>
                                <th>{{ $f('reason') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr wire:key="vdemand-{{ $row->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-center">{{ $dateText($row->demand_date) }}</td>
                                    <td class="text-center font-weight-bold crud-wrap">{{ $row->vehicle?->vehicle_no ?? __('vehicle.none') }}</td>
                                    @if ($showDriver)<td class="text-center crud-wrap">{{ $row->driver?->label ?? __('vehicle.none') }}</td>@endif
                                    <td class="text-center"><span class="badge {{ $typeTone[$row->demand_type] ?? 'badge-secondary' }}" style="font-size: 0.8125rem;">{{ $typeOptions[$row->demand_type] ?? $row->demand_type }}</span></td>
                                    <td class="text-center">{{ $amount($row) }}</td>
                                    <td class="crud-wrap">{{ \Illuminate\Support\Str::limit((string) $row->reason, 90) }}</td>
                                    @include('livewire.vehicles.partials.row-actions', ['id' => $row->id, 'label' => $row->vehicle?->vehicle_no ?? ''])
                                </tr>
                            @empty
                                <tr wire:key="vdemand-empty">
                                    <td colspan="{{ $showDriver ? 8 : 7 }}" class="crud-empty">{{ $searching ? __('commontable.dt_zero_records') : __('commontable.no_record_found') }}</td>
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
            <x-ui.modal wire:model="showForm" max-width="2xl" labelledby="vdemand-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    @include('livewire.vehicles.partials.modal-open', ['titleId' => 'vdemand-form-title'])
                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form') <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div> @enderror
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            @include('livewire.vehicles.partials.search-select', ['name' => 'vehicleId', 'label' => $f('vehicle'), 'required' => true, 'options' => $this->vehicleChoices])
                            @include('livewire.vehicles.partials.field', ['name' => 'demandDate', 'label' => $f('demand_date'), 'type' => 'date', 'required' => true])
                            @include('livewire.vehicles.partials.search-select', ['name' => 'demandType', 'label' => $f('demand_type'), 'required' => true, 'options' => $typeOptions, 'wide' => true, 'live' => true])
                            @include('livewire.vehicles.partials.field', ['name' => 'quantity', 'label' => $f('quantity'), 'type' => 'number'])
                            {{-- The units depend on the demand type: the field is rebuilt when the type changes --}}
                            <div wire:key="demand-unit-{{ $demandType ?: 'all' }}">
                                @include('livewire.vehicles.partials.search-select', ['name' => 'unit', 'label' => $f('unit'), 'options' => $this->unitChoices])
                            </div>
                            @include('livewire.vehicles.partials.field', ['name' => 'reason', 'label' => $f('reason'), 'type' => 'textarea', 'required' => true, 'max' => 1000, 'wide' => true])
                        </div>
                    </div>
                    @include('livewire.vehicles.partials.modal-foot')
                </form>
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="vdemand-delete"
                :title="__('vehicle.delete_title')" :text="__('vehicle.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
