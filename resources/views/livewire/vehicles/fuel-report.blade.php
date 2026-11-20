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
                                            <span class="d-block font-weight-bold">{{ $v ? $digits($v->vehicle_no) : '#' . $digits($id) }}</span>
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
                                        <td class="text-center font-weight-bold" style="min-width: 8rem;">{{ $this->monthText($ym) }}</td>
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
</div>
