@php
    $byVehicle = $report['byVehicle'];
    $byMonth = $report['byMonth'];
    $total = $report['total'];
    $vehicles = $report['vehicles'];
    $chosenVehicle = $vehicleId !== '' ? ($this->vehicleChoices[$vehicleId] ?? '') : __('vehicle.fuel_report.all_vehicles');
    $period = $year !== '' ? $digits($year) : __('vehicle.fuel_report.all_time');
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)" class="fuel-report">
    {{-- Printing: only the report, on white paper --}}
    <style>
        @media print {
            .main-sidebar, .main-header, .main-footer, .fuel-report .no-print, .low-stock-marquee, .content-header .breadcrumb { display: none !important; }
            .content-wrapper { margin-left: 0 !important; background: #fff !important; }
            .fuel-report .card { box-shadow: none !important; border: 0 !important; }
            .fuel-report .table-responsive { overflow: visible !important; }
            .fuel-report .print-only { display: block !important; }
            .fuel-report table { font-size: 13px; }
        }
        .fuel-report .print-only { display: none; }
    </style>

    @include('livewire.vehicles.partials.page-top')

    <div class="content px-3">
        <div class="card card-primary no-print">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h3 class="card-title mb-0 mr-2">{{ $t('filter') }}</h3>
                    <button type="button" class="btn btn-sm btn-secondary crud-btn" onclick="window.print()" title="{{ $t('print') }}" aria-label="{{ $t('print') }}">
                        <i class="fas fa-print" aria-hidden="true"></i><span class="d-none d-sm-inline ml-1">{{ $t('print') }}</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="tw-root">
                    <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2 tw-font-sans tw-text-slate-800">
                        @include('livewire.vehicles.partials.search-select', ['name' => 'vehicleId', 'label' => $t('vehicle'), 'options' => $this->vehicleChoices, 'live' => true, 'hint' => $t('vehicle_hint')])
                        @include('livewire.vehicles.partials.search-select', ['name' => 'year', 'label' => $t('year'), 'options' => $this->yearChoices, 'live' => true])
                    </div>
                </div>
                <p class="text-muted small mb-0 mt-3"><i class="fas fa-info-circle mr-1" aria-hidden="true"></i>{{ $t('source_note') }}</p>
            </div>
        </div>

        <div class="print-only mb-3">
            <h4 class="mb-1">{{ $t('title') }}</h4>
            <div>{{ $t('vehicle') }}: {{ $chosenVehicle }} · {{ $t('year') }}: {{ $period }}</div>
        </div>

        <div class="position-relative">
            <div class="crud-loading" wire:loading.flex wire:target="vehicleId, year">
                <span class="badge badge-light p-2"><i class="fas fa-circle-notch fa-spin mr-1" aria-hidden="true"></i>{{ __('commontable.loading') }}</span>
            </div>

            {{-- Total per vehicle --}}
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">{{ $t('by_vehicle') }} <small class="text-muted">({{ $period }})</small></h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table crud-table" id="fuel-by-vehicle">
                            <thead>
                                <tr>
                                    <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                    <th>{{ $t('vehicle_no') }}</th>
                                    <th>{{ $t('from_requisitions') }}</th>
                                    <th>{{ $t('from_demands') }}</th>
                                    <th>{{ $t('total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($byVehicle as $id => $sums)
                                    @php $v = $vehicles[$id] ?? null; @endphp
                                    <tr wire:key="fuel-v-{{ $id }}">
                                        <td class="crud-serial">{{ $digits($loop->iteration) }}</td>
                                        <td class="text-center crud-wrap" style="min-width: 9rem;">
                                            <button type="button" wire:click="openVehicle({{ (int) $id }})" class="btn btn-link p-0 d-block mx-auto font-weight-bold text-body" style="text-decoration: underline dotted; text-underline-offset: 3px;"
                                                title="{{ $t('vehicle_history') }}" aria-label="{{ $t('vehicle_history') }}: {{ $v ? $digits($v->vehicle_no) : '#' . $digits($id) }}">{{ $v ? $digits($v->vehicle_no) : '#' . $digits($id) }}</button>
                                            @if ($v?->type)<small class="text-muted">{{ $v->type->label }}</small>@endif
                                        </td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($sums['req'] ?? []) }}@if (! empty($sums['times_req']))<small class="d-block text-muted">{{ $digits($sums['times_req']) }} {{ $t('times') }}</small>@endif</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($sums['dem'] ?? []) }}@if (! empty($sums['times_dem']))<small class="d-block text-muted">{{ $digits($sums['times_dem']) }} {{ $t('times') }}</small>@endif</td>
                                        <td class="text-center text-nowrap font-weight-bold">{{ $this->amountText($sums['all'] ?? []) }}</td>
                                    </tr>
                                @empty
                                    <tr wire:key="fuel-v-empty"><td colspan="5" class="crud-empty">{{ $t('none') }}</td></tr>
                                @endforelse
                            </tbody>
                            @if (count($byVehicle) > 1)
                                <tfoot>
                                    <tr class="font-weight-bold" style="background: rgba(0,0,0,.03);">
                                        <td colspan="2" class="text-right">{{ $t('grand_total') }}</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($total['req'] ?? []) }}</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($total['dem'] ?? []) }}</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($total['all'] ?? []) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Month by month --}}
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title mb-0">{{ $t('by_month') }} <small class="text-muted">({{ $chosenVehicle }} · {{ $period }})</small></h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table crud-table" id="fuel-by-month">
                            <thead>
                                <tr>
                                    <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                    <th>{{ $t('month') }}</th>
                                    <th>{{ $t('from_requisitions') }}</th>
                                    <th>{{ $t('from_demands') }}</th>
                                    <th>{{ $t('total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($byMonth as $ym => $sums)
                                    <tr wire:key="fuel-m-{{ $ym }}" @if (empty($sums['all'])) class="text-muted" @endif>
                                        <td class="crud-serial">{{ $digits($loop->iteration) }}</td>
                                        <td class="text-center font-weight-bold" style="min-width: 8rem;">
                                            @if (! empty($sums['all']))
                                                <button type="button" wire:click="openMonth('{{ $ym }}')" class="btn btn-link p-0 font-weight-bold" style="text-decoration: underline dotted; text-underline-offset: 3px;"
                                                    title="{{ $t('month_details') }}" aria-label="{{ $t('month_details') }}: {{ $this->monthText($ym) }}">{{ $this->monthText($ym) }}</button>
                                            @else
                                                {{ $this->monthText($ym) }}
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($sums['req'] ?? []) }}</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($sums['dem'] ?? []) }}</td>
                                        <td class="text-center text-nowrap font-weight-bold">{{ $this->amountText($sums['all'] ?? []) }}</td>
                                    </tr>
                                @empty
                                    <tr wire:key="fuel-m-empty"><td colspan="5" class="crud-empty">{{ $t('none') }}</td></tr>
                                @endforelse
                            </tbody>
                            @if (! empty($total['all']))
                                <tfoot>
                                    <tr class="font-weight-bold" style="background: rgba(0,0,0,.03);">
                                        <td colspan="2" class="text-right">{{ $t('grand_total') }}</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($total['req'] ?? []) }}</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($total['dem'] ?? []) }}</td>
                                        <td class="text-center text-nowrap">{{ $this->amountText($total['all'] ?? []) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- The fuel entries of one month --}}
    <div class="tw-root no-print">
        <div class="tw-font-sans tw-text-slate-800">
            @php $ui = \App\Livewire\Concerns\Ui::class; $entries = $showMonth ? $this->monthEntries : []; $dateText = fn ($d) => $d ? $digits($d->copy()->locale(app()->getLocale() === 'bn' ? 'bn' : 'en')->translatedFormat(app()->getLocale() === 'bn' ? 'j F Y, l' : 'd M Y, D')) : '—'; @endphp
            <x-ui.modal wire:model="showMonth" max-width="4xl" labelledby="fuel-month-title">
                <div class="{{ $ui::MODAL_HEADER }}">
                    <div class="tw-min-w-0 tw-flex-1">
                        <h2 id="fuel-month-title" class="{{ $ui::MODAL_TITLE }}">{{ $showMonth ? $this->detailTitle() : '' }} <span class="tw-text-sm tw-font-semibold tw-text-slate-500">({{ $detailVehicle ? $period : $chosenVehicle }})</span></h2>
                    </div>
                    <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="{{ $ui::MODAL_BODY }}">
                    @if (count($entries))
                        <div class="tw-overflow-x-auto">
                            <table class="table crud-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                        <th>{{ $t('date') }}</th>
                                        @if ($vehicleId === '' && ! $detailVehicle)<th>{{ $t('vehicle_no') }}</th>@endif
                                        <th>{{ $t('source') }}</th>
                                        <th>{{ $t('amount') }}</th>
                                        <th>{{ $t('driver') }}</th>
                                        <th>{{ $t('details') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($entries as $e)
                                        <tr wire:key="fuel-e-{{ $e['source'] }}-{{ $e['id'] }}">
                                            <td class="crud-serial">{{ $digits($loop->iteration) }}</td>
                                            <td class="text-center text-nowrap font-weight-bold">{{ $dateText($e['date']) }}</td>
                                            @if ($vehicleId === '' && ! $detailVehicle)<td class="text-center text-nowrap">{{ $e['vehicle'] ? $digits($e['vehicle']) : '—' }}</td>@endif
                                            <td class="text-center"><span class="badge {{ $e['source'] === 'req' ? 'badge-info' : 'badge-warning' }}" style="font-size: .8125rem;">{{ $e['source'] === 'req' ? $t('from_requisitions') : $t('from_demands') }}</span></td>
                                            <td class="text-center text-nowrap font-weight-bold">{{ $this->amountText($e['amount']) }}</td>
                                            <td class="text-center crud-wrap" style="min-width: 7.5rem;">{{ $e['driver'] ?: '—' }}</td>
                                            <td class="crud-wrap" style="min-width: 10rem;">{{ \Illuminate\Support\Str::limit($e['details'], 90) ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="{{ $ui::HINT }}">{{ $t('none') }}</p>
                    @endif
                </div>
                <div class="{{ $ui::MODAL_FOOTER }}">
                    <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('commontable.close') }}</button>
                </div>
            </x-ui.modal>
        </div>
    </div>
</div>
