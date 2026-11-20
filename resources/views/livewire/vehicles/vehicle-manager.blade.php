@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $isBn = app()->getLocale() === 'bn';
    $dateText = fn ($date) => $date ? $digits($date->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat($isBn ? 'j F Y' : 'd M Y')) : __('vehicle.none');
    $deptName = fn ($officer) => $officer->department ? ($isBn && $officer->department->name_bn ? $officer->department->name_bn : $officer->department->name) : null;
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    @include('livewire.vehicles.partials.page-top')

    <div class="content px-3">
        <div class="card card-primary">
            @include('livewire.vehicles.partials.card-head')
            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', ['id' => 'vehicle', 'placeholder' => $t('search')])
                @include('livewire.vehicles.partials.loading')

                <div class="table-responsive">
                    <table class="table crud-table" id="vehicles-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ $f('vehicle_no') }}</th>
                                <th>{{ $f('vehicle_type') }}</th>
                                <th>{{ $f('driver') }}</th>
                                <th>{{ $f('driving_date') }}</th>
                                <th>{{ $f('route') }}</th>
                                <th>{{ $f('officers') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr wire:key="vehicle-{{ $row->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-center font-weight-bold crud-wrap">{{ $row->vehicle_no }}</td>
                                    <td class="text-center crud-wrap">{{ $row->type?->label ?? __('vehicle.none') }}</td>
                                    <td class="text-center crud-wrap">{{ $row->driver?->label ?? __('vehicle.none') }}</td>
                                    <td class="text-center">{{ $dateText($row->driving_date) }}</td>
                                    <td class="text-center crud-wrap">{{ $row->route?->name ?? __('vehicle.none') }}</td>
                                    <td class="crud-wrap">
                                        @forelse ($row->officers as $officer)
                                            <span class="badge badge-light border mb-1" style="font-size: 0.8125rem; font-weight: 600;">{{ $officer->label }}@if ($d = $deptName($officer)) · {{ $d }}@endif</span>
                                        @empty
                                            {{ __('vehicle.none') }}
                                        @endforelse
                                    </td>
                                    @include('livewire.vehicles.partials.row-actions', ['id' => $row->id, 'label' => $row->vehicle_no, 'view' => true])
                                </tr>
                            @empty
                                <tr wire:key="vehicle-empty">
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
            {{-- ============ Add / edit ============ --}}
            <x-ui.modal wire:model="showForm" max-width="3xl" labelledby="vehicle-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    @include('livewire.vehicles.partials.modal-open', ['titleId' => 'vehicle-form-title'])
                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form') <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div> @enderror
                        @php $choices = $this->choices; @endphp
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            @include('livewire.vehicles.partials.field', ['name' => 'vehicleNo', 'label' => $f('vehicle_no'), 'required' => true, 'max' => 50])
                            @include('livewire.vehicles.partials.search-select', ['name' => 'typeId', 'label' => $f('vehicle_type'), 'required' => true, 'options' => $choices['types']])
                            @include('livewire.vehicles.partials.search-select', ['name' => 'driverId', 'label' => $f('driver'), 'options' => $choices['drivers'], 'disabled' => $choices['busyDrivers'], 'hint' => count($choices['drivers']) ? null : $t('drivers_none')])
                            @include('livewire.vehicles.partials.field', ['name' => 'drivingDate', 'label' => $f('driving_date'), 'type' => 'date'])
                            @include('livewire.vehicles.partials.search-select', ['name' => 'routeId', 'label' => $f('route'), 'options' => $choices['routes'], 'wide' => true])

                            {{-- Offices / officers: any number, with a filter --}}
                            <div class="sm:tw-col-span-2" x-data="{ q: '' }">
                                <div class="tw-mb-1.5 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2">
                                    <span class="{{ $ui::LABEL }} !tw-mb-0">{{ $f('officers') }}</span>
                                    <span class="tw-rounded-full tw-bg-brand-50 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-brand-700">
                                        {{ str_replace(':count', $digits(count($officerIds)), $t('selected')) }}
                                    </span>
                                </div>
                                @if (count($choices['officers']))
                                    <input type="search" x-model="q" placeholder="{{ $t('officer_filter') }}" aria-label="{{ $t('officer_filter') }}" class="{{ $ui::INPUT }} tw-mb-2">
                                    <div class="tw-max-h-56 tw-overflow-y-auto tw-rounded-lg tw-border tw-border-solid tw-border-slate-200 tw-p-1">
                                        @foreach ($choices['officers'] as $officer)
                                            <label wire:key="vo-{{ $officer['id'] }}"
                                                x-show="!q || @js(mb_strtolower($officer['name'] . ' ' . $officer['meta'])).includes(q.toLowerCase())"
                                                class="tw-m-0 tw-flex tw-cursor-pointer tw-items-start tw-gap-3 tw-rounded-md tw-px-2.5 tw-py-2 hover:tw-bg-slate-50">
                                                <input type="checkbox" value="{{ $officer['id'] }}" wire:model.live="officerIds" class="tw-mt-1 tw-h-4 tw-w-4 tw-flex-none tw-accent-brand-600">
                                                <span class="tw-min-w-0">
                                                    <span class="tw-block tw-text-sm tw-font-semibold tw-text-slate-800">{{ $officer['name'] }}</span>
                                                    @if ($officer['meta'])<span class="tw-block tw-text-xs tw-text-slate-500">{{ $officer['meta'] }}</span>@endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="{{ $ui::HINT }}">{{ $t('officers_none') }}</p>
                                @endif
                                @error('officerIds.*') <p class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    @include('livewire.vehicles.partials.modal-foot')
                </form>
            </x-ui.modal>

            {{-- ============ Details ============ --}}
            <x-ui.modal wire:model="showView" max-width="2xl" labelledby="vehicle-view-title">
                @if ($vehicle = $this->viewing)
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="vehicle-view-title" class="{{ $ui::MODAL_TITLE }}">{{ $t('view') }}</h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ $vehicle->vehicle_no }}</p>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="{{ $ui::MODAL_BODY }}">
                        <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-2">
                            @foreach ([
                                $f('vehicle_no') => $vehicle->vehicle_no,
                                $f('vehicle_type') => $vehicle->type?->label ?? __('vehicle.none'),
                                $f('driver') => $vehicle->driver ? $vehicle->driver->label . ($vehicle->driver->mobile ? ' · ' . $digits($vehicle->driver->mobile) : '') : __('vehicle.none'),
                                $f('driving_date') => $dateText($vehicle->driving_date),
                                $f('route') => $vehicle->route ? $vehicle->route->name . (($vehicle->route->start_point || $vehicle->route->end_point) ? ' (' . ($vehicle->route->start_point ?: '—') . ' → ' . ($vehicle->route->end_point ?: '—') . ')' : '') : __('vehicle.none'),
                            ] as $term => $value)
                                <div class="tw-min-w-0 tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                                    <dt class="tw-text-xs tw-font-semibold tw-text-slate-500">{{ $term }}</dt>
                                    <dd class="tw-m-0 tw-mt-0.5 tw-break-words tw-text-sm tw-font-semibold tw-text-slate-900">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <div>
                            <p class="{{ $ui::LABEL }}">{{ $f('officers') }}</p>
                            @if ($vehicle->officers->count())
                                <ul class="tw-m-0 tw-grid tw-list-none tw-grid-cols-1 tw-gap-2 tw-p-0 sm:tw-grid-cols-2">
                                    @foreach ($vehicle->officers as $officer)
                                        <li class="tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 tw-px-3 tw-py-2">
                                            <span class="tw-block tw-text-sm tw-font-semibold tw-text-slate-900">{{ $officer->label }}</span>
                                            <span class="tw-block tw-text-xs tw-text-slate-500">{{ collect([$officer->designation, $deptName($officer), $officer->mobile ? $digits($officer->mobile) : null])->filter()->join(' · ') ?: '—' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="{{ $ui::HINT }}">{{ __('vehicle.none') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('commontable.close') }}</button>
                    </div>
                @endif
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="vehicle-delete"
                :title="__('vehicle.delete_title')" :text="__('vehicle.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
