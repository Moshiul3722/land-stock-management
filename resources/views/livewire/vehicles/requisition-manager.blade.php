@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $isBn = app()->getLocale() === 'bn';
    $none = __('vehicle.none');
    $dateText = fn ($date) => $date ? $digits($date->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat($isBn ? 'j F Y' : 'd M Y')) : $none;
    $timeText = function ($time) use ($digits, $isBn) {
        if (! $time) {
            return null;
        }
        $at = \Illuminate\Support\Carbon::createFromFormat('H:i', substr((string) $time, 0, 5));

        return $digits($at->format('g:i')) . ' ' . ($isBn ? ($at->hour < 12 ? 'পূর্বাহ্ণ' : 'অপরাহ্ণ') : $at->format('A'));
    };
    $number = fn ($value) => $value !== null ? $digits(rtrim(rtrim((string) $value, '0'), '.')) : null;
    $statusTone = ['pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-danger'];
    $purposeOptions = collect(\App\Models\VehicleRequisition::PURPOSES)->mapWithKeys(fn ($p) => [$p => $t('purposes.' . $p)])->all();
    $decider = $this->isDecider;
    $choices = $showDecide ? $this->decisionChoices : ['vehicles' => [], 'drivers' => [], 'units' => []];
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    @include('livewire.vehicles.partials.page-top')

    <div class="content px-3">
        <div class="card card-primary">
            @include('livewire.vehicles.partials.card-head')
            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', ['id' => 'vreq', 'placeholder' => $t('search')])
                @include('livewire.vehicles.partials.loading')

                <div class="table-responsive">
                    <table class="table crud-table" id="vehicle-requisitions-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ $t('report_place') }}</th>
                                @if ($decider)<th>{{ $t('applicant') }}</th>@endif
                                <th>{{ $t('destination') }}</th>
                                <th>{{ $t('purpose') }}</th>
                                <th>{{ $t('vehicle_no') }}</th>
                                <th>{{ $t('status') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr wire:key="vreq-{{ $row->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-center crud-wrap" style="min-width: 8.5rem;">
                                        <span class="d-block font-weight-bold">{{ $dateText($row->report_date) }}</span>
                                        <small class="text-muted">{{ collect([$timeText($row->report_time), $row->report_place])->filter()->join(' · ') }}</small>
                                    </td>
                                    @if ($decider)
                                        <td class="text-center crud-wrap" style="min-width: 8.5rem;">
                                            <span class="d-block">{{ $row->applicant_name }}</span>
                                            @if ($row->applicant_designation)<small class="text-muted">{{ $row->applicant_designation }}</small>@endif
                                        </td>
                                    @endif
                                    <td class="crud-wrap" style="min-width: 10rem;">{{ \Illuminate\Support\Str::limit((string) $row->destination, 70) }}</td>
                                    <td class="text-center"><span class="badge {{ $row->purpose === 'official' ? 'badge-info' : 'badge-secondary' }}" style="font-size: 0.8125rem;">{{ $purposeOptions[$row->purpose] ?? $row->purpose }}</span></td>
                                    <td class="text-center crud-wrap" style="min-width: 7.5rem;">
                                        @if ($row->vehicle)
                                            <span class="d-block font-weight-bold">{{ $row->vehicle->vehicle_no }}</span>
                                            <small class="text-muted">{{ $row->driver?->label }}</small>
                                        @else
                                            {{ $none }}
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="badge {{ $statusTone[$row->status] ?? 'badge-secondary' }}" style="font-size: 0.8125rem;">{{ $t('statuses.' . $row->status) }}</span></td>
                                    <td class="crud-actions">
                                        <button type="button" class="btn btn-sm btn-info crud-btn" wire:click="openView({{ $row->id }})"
                                            data-tip="{{ __('vehicle.view') }}" aria-label="{{ __('vehicle.view') }}: {{ $row->destination }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                        @if ($decider && $row->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success crud-btn" wire:click="openDecide({{ $row->id }})"
                                                data-tip="{{ $t('decide') }}" aria-label="{{ $t('decide') }}: {{ $row->destination }}">
                                                <i class="fas fa-check-double" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                        <a class="btn btn-sm btn-secondary crud-btn" href="{{ route('vehicle-requisitions.print', $row->id) }}?preview=1" target="_blank" rel="noopener"
                                            data-tip="{{ $t('print_preview') }}" aria-label="{{ $t('print_preview') }}: {{ $row->destination }}">
                                            <i class="fas fa-print" aria-hidden="true"></i>
                                        </a>
                                        @if ($row->status === 'pending' && (int) $row->user_id === (int) auth()->id())
                                            <button type="button" class="btn btn-sm btn-primary crud-btn" wire:click="openEdit({{ $row->id }})"
                                                data-tip="{{ __('buttons.edit') }}" aria-label="{{ __('buttons.edit') }}: {{ $row->destination }}">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger crud-btn" wire:click="confirmDelete({{ $row->id }})"
                                                data-tip="{{ __('buttons.delete') }}" aria-label="{{ __('buttons.delete') }}: {{ $row->destination }}">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr wire:key="vreq-empty">
                                    <td colspan="{{ $decider ? 8 : 7 }}" class="crud-empty">{{ $searching ? __('commontable.dt_zero_records') : __('commontable.no_record_found') }}</td>
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
            {{-- Apply / edit --}}
            <x-ui.modal wire:model="showForm" max-width="2xl" labelledby="vreq-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    @include('livewire.vehicles.partials.modal-open', ['titleId' => 'vreq-form-title'])
                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form') <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div> @enderror
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            <fieldset class="tw-m-0 tw-border-0 tw-p-0 sm:tw-col-span-2">
                                <legend class="{{ $ui::LABEL }}">{{ $t('purpose') }} <span class="tw-text-rose-600">*</span></legend>
                                <div class="tw-grid tw-grid-cols-2 tw-gap-2">
                                    @foreach ($purposeOptions as $value => $text)
                                        <label class="tw-m-0 tw-flex tw-min-h-11 tw-cursor-pointer tw-items-center tw-gap-2 tw-rounded-lg tw-border tw-border-solid tw-px-3 tw-text-sm tw-font-semibold tw-transition"
                                            x-bind:class="$wire.purpose === @js($value) ? 'tw-border-brand-500 tw-bg-brand-50 tw-text-brand-700' : 'tw-border-slate-300 tw-bg-white tw-text-slate-700'">
                                            <input type="radio" wire:model.live="purpose" value="{{ $value }}" class="tw-h-4 tw-w-4">
                                            {{ $loop->first ? ($isBn ? 'ক.' : 'a.') : ($isBn ? 'খ.' : 'b.') }} {{ $text }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('purpose') <p class="{{ $ui::ERROR }}">{{ $message }}</p> @enderror
                                @if ($purpose === 'personal') <p class="{{ $ui::HINT }}"><i class="fas fa-info-circle tw-mr-1" aria-hidden="true"></i>{{ $t('personal_note') }}</p> @endif
                            </fieldset>
                            @include('livewire.vehicles.partials.field', ['name' => 'officialDetails', 'label' => $t('official_details'), 'type' => 'textarea', 'required' => $purpose === 'official', 'max' => 1000, 'wide' => true])
                            @include('livewire.vehicles.partials.field', ['name' => 'reportDate', 'label' => $t('report_date'), 'type' => 'date', 'required' => true])
                            @include('livewire.vehicles.partials.field', ['name' => 'reportTime', 'label' => $t('report_time_field'), 'type' => 'time', 'required' => true])
                            @include('livewire.vehicles.partials.field', ['name' => 'reportPlace', 'label' => $t('report_place'), 'required' => true, 'max' => 255, 'blur' => true])
                            @include('livewire.vehicles.partials.field', ['name' => 'destination', 'label' => $t('destination'), 'required' => true, 'max' => 255, 'blur' => true])
                            <div>
                                @include('livewire.vehicles.partials.field', ['name' => 'distanceKm', 'label' => $t('distance') . ' (' . $t('km') . ', ' . $t('round_trip') . ')', 'type' => 'number'])
                                <div class="tw-mt-1.5 tw-flex tw-flex-wrap tw-items-center tw-gap-x-3 tw-gap-y-1">
                                    <button type="button" wire:click="calculateDistance" wire:loading.attr="disabled" wire:target="calculateDistance, destination, reportPlace"
                                        class="tw-inline-flex tw-items-center tw-gap-1.5 tw-border-0 tw-bg-transparent tw-p-0 tw-text-xs tw-font-semibold tw-text-brand-700 hover:tw-underline disabled:tw-opacity-50">
                                        <i class="fas fa-route" aria-hidden="true"></i>{{ $t('distance_calc') }}
                                    </button>
                                    <span class="tw-text-xs tw-text-slate-500" wire:loading wire:target="calculateDistance, destination, reportPlace">
                                        <i class="fas fa-circle-notch fa-spin tw-mr-1" aria-hidden="true"></i>{{ $t('distance_calculating') }}
                                    </span>
                                </div>
                                @if ($distanceNote !== '')
                                    <p class="{{ $ui::HINT }} {{ $distanceFailed ? '!tw-text-amber-700' : '!tw-text-emerald-700' }}" wire:loading.remove wire:target="calculateDistance, destination, reportPlace" role="status">{{ $distanceNote }}</p>
                                @endif
                            </div>
                            @include('livewire.vehicles.partials.field', ['name' => 'returnTime', 'label' => $t('return_time'), 'type' => 'time'])
                        </div>
                    </div>
                    @include('livewire.vehicles.partials.modal-foot')
                </form>
            </x-ui.modal>

            {{-- View, with print preview / print --}}
            <x-ui.modal wire:model="showView" max-width="2xl" labelledby="vreq-view-title">
                @if ($req = $this->viewing)
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="vreq-view-title" class="{{ $ui::MODAL_TITLE }}">{{ $t('view') }}</h2>
                            <p class="{{ $ui::MODAL_HINT }}">#{{ $digits($req->id) }} · <span class="badge {{ $statusTone[$req->status] ?? 'badge-secondary' }}">{{ $t('statuses.' . $req->status) }}</span></p>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="{{ $ui::MODAL_BODY }}">
                        <dl class="tw-m-0 tw-grid tw-grid-cols-1 tw-gap-3 sm:tw-grid-cols-2">
                            @foreach ([
                                $t('applicant_name') => $req->applicant_name,
                                $t('designation') => $req->applicant_designation ?: $none,
                                $t('purpose') => $purposeOptions[$req->purpose] ?? $req->purpose,
                                $t('destination') => $req->destination,
                                $t('official_details') => $req->official_details ?: $none,
                                $t('distance') => $req->distance_km !== null ? $number($req->distance_km) . ' ' . $t('km') : $none,
                                $t('return_time') => $timeText($req->return_time) ?? $none,
                                $t('report_place') => $req->report_place,
                                $t('report_date') . ' / ' . $t('report_time') => $dateText($req->report_date) . ' · ' . ($timeText($req->report_time) ?? ''),
                                $t('vehicle_no') => $req->vehicle ? $req->vehicle->vehicle_no . ($req->vehicle->type ? ' · ' . $req->vehicle->type->label : '') : $none,
                                $t('driver_name') => $req->driver ? $req->driver->label . ($req->driver->mobile ? ' · ' . $digits($req->driver->mobile) : '') : $none,
                                $t('fuel_amount') => $req->fuel_amount !== null ? trim($number($req->fuel_amount) . ' ' . $req->fuel_unit) : $none,
                            ] as $term => $value)
                                <div class="tw-min-w-0 tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3">
                                    <dt class="tw-text-xs tw-font-semibold tw-text-slate-500">{{ $term }}</dt>
                                    <dd class="tw-m-0 tw-mt-0.5 tw-break-words tw-text-sm tw-font-semibold tw-text-slate-900">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        @if ($req->status !== 'pending')
                            <div class="tw-rounded-xl tw-border tw-border-solid {{ $req->status === 'approved' ? 'tw-border-emerald-200 tw-bg-emerald-50' : 'tw-border-rose-200 tw-bg-rose-50' }} tw-px-4 tw-py-3 tw-text-sm">
                                <p class="tw-m-0 tw-font-semibold">{{ $t('decided_by') }}: {{ $req->decider ? (($isBn && $req->decider->name_bn) ? $req->decider->name_bn : $req->decider->name) : $none }} · {{ $req->decided_at ? $digits($req->decided_at->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat($isBn ? 'j F Y, g:i A' : 'd M Y, g:i A')) : '' }}</p>
                                @if ($req->decision_note)<p class="tw-mb-0 tw-mt-1">{{ $t('decision_note') }}: {{ $req->decision_note }}</p>@endif
                            </div>
                        @endif
                    </div>
                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('commontable.close') }}</button>
                        <a href="{{ route('vehicle-requisitions.print', $req->id) }}?preview=1" target="_blank" rel="noopener" class="{{ $ui::BTN_SECONDARY }} tw-no-underline">
                            <i class="fas fa-file-alt" aria-hidden="true"></i>{{ $t('print_preview') }}
                        </a>
                        <a href="{{ route('vehicle-requisitions.print', $req->id) }}" target="_blank" rel="noopener" class="{{ $ui::BTN_PRIMARY }} tw-no-underline hover:tw-text-white">
                            <i class="fas fa-print" aria-hidden="true"></i>{{ $t('print') }}
                        </a>
                        @if ($decider && $req->status === 'pending')
                            <button type="button" wire:click="openDecide({{ $req->id }})" class="{{ $ui::BTN_PRIMARY }} !tw-border-emerald-600 !tw-bg-emerald-600 hover:!tw-bg-emerald-700">
                                <i class="fas fa-check-double" aria-hidden="true"></i>{{ $t('decide') }}
                            </button>
                        @endif
                    </div>
                @endif
            </x-ui.modal>

            {{-- Super Admin / Admin: approve with vehicle, driver and fuel, or reject --}}
            @if ($decider)
                <x-ui.modal wire:model="showDecide" max-width="2xl" labelledby="vreq-decide-title">
                    @if ($req = $this->deciding)
                        <form wire:submit="approve" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                            <div class="{{ $ui::MODAL_HEADER }}">
                                <div class="tw-min-w-0 tw-flex-1">
                                    <h2 id="vreq-decide-title" class="{{ $ui::MODAL_TITLE }}">{{ $t('decide') }}</h2>
                                    <p class="{{ $ui::MODAL_HINT }}">{{ $t('decide_hint') }}</p>
                                </div>
                                <x-ui.modal-today />
                                <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="{{ $ui::MODAL_BODY }}">
                                <div class="tw-rounded-xl tw-bg-slate-50 tw-px-4 tw-py-3 tw-text-sm">
                                    <p class="tw-m-0 tw-font-semibold tw-text-slate-900">{{ $req->applicant_name }}@if ($req->applicant_designation), {{ $req->applicant_designation }}@endif</p>
                                    <p class="tw-mb-0 tw-mt-1 tw-text-slate-600">
                                        <span class="badge {{ $req->purpose === 'official' ? 'badge-info' : 'badge-secondary' }}">{{ $purposeOptions[$req->purpose] ?? '' }}</span>
                                        {{ $req->destination }} · {{ $dateText($req->report_date) }} {{ $timeText($req->report_time) }} · {{ $req->report_place }}
                                    </p>
                                </div>
                                <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                                    @include('livewire.vehicles.partials.search-select', ['name' => 'decVehicleId', 'label' => $t('vehicle_no'), 'required' => true, 'options' => $choices['vehicles'], 'live' => true])
                                    @include('livewire.vehicles.partials.search-select', ['name' => 'decDriverId', 'label' => $t('driver_name'), 'required' => true, 'options' => $choices['drivers']])
                                    @include('livewire.vehicles.partials.field', ['name' => 'decFuelAmount', 'label' => $t('fuel_amount'), 'type' => 'number'])
                                    @include('livewire.vehicles.partials.search-select', ['name' => 'decFuelUnit', 'label' => $t('fuel_unit'), 'options' => $choices['units']])
                                    @include('livewire.vehicles.partials.field', ['name' => 'decNote', 'label' => $t('decision_note'), 'type' => 'textarea', 'max' => 1000, 'wide' => true])
                                </div>
                            </div>
                            <div class="{{ $ui::MODAL_FOOTER }}">
                                <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('buttons.cancel') }}</button>
                                <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject, approve" class="{{ $ui::BTN_DANGER }}">
                                    <i class="fas fa-times-circle" aria-hidden="true"></i>{{ $t('reject') }}
                                </button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="reject, approve" class="{{ $ui::BTN_PRIMARY }} !tw-border-emerald-600 !tw-bg-emerald-600 hover:!tw-bg-emerald-700">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>{{ $t('approve') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </x-ui.modal>
            @endif

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="vreq-delete"
                :title="__('vehicle.delete_title')" :text="__('vehicle.delete_text')" :name="$deleteLabel" />
        </div>
    </div>
</div>
