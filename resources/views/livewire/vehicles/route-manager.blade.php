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
                            {{-- Route name: the places in order, joined with arrows --}}
                            <div class="sm:tw-col-span-2">
                                <p class="{{ $ui::LABEL }}">{{ $f('route_name') }} <span class="tw-text-rose-600">*</span></p>
                                <ol class="tw-m-0 tw-list-none tw-space-y-1 tw-p-0">
                                    @foreach ($stops as $i => $stop)
                                        <li wire:key="route-stop-{{ $i }}-{{ count($stops) }}">
                                            @if ($i > 0)
                                                <div class="tw-flex tw-justify-center tw-pb-1 tw-text-brand-500" aria-hidden="true"><i class="fas fa-arrow-down"></i></div>
                                            @endif
                                            <div class="tw-flex tw-items-center tw-gap-2">
                                                <span class="tw-inline-flex tw-h-8 tw-w-8 tw-flex-none tw-items-center tw-justify-center tw-rounded-full tw-bg-brand-50 tw-text-sm tw-font-bold tw-text-brand-700">{{ $digits($i + 1) }}</span>
                                                <input id="vf-stop-{{ $i }}" type="text" wire:model="stops.{{ $i }}" maxlength="100" autocomplete="off"
                                                    placeholder="{{ __('vehicle.routes.stop', ['n' => $digits($i + 1)]) }}" aria-label="{{ __('vehicle.routes.stop', ['n' => $digits($i + 1)]) }}"
                                                    class="{{ $ui::INPUT }} tw-min-w-0 tw-flex-1 @error('stops.' . $i) {{ $ui::INPUT_ERROR }} @enderror">
                                                @if (count($stops) > 2)
                                                    <button type="button" wire:click="removeStop({{ $i }})" class="{{ $ui::ICON_BTN }} tw-flex-none !tw-text-rose-600 hover:!tw-bg-rose-50"
                                                        aria-label="{{ __('vehicle.routes.remove_stop') }}" title="{{ __('vehicle.routes.remove_stop') }}">
                                                        <i class="fas fa-times" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            @error('stops.' . $i) <p class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                                        </li>
                                    @endforeach
                                </ol>
                                @if (count($stops) < 10)
                                    <button type="button" wire:click="addStop" class="{{ $ui::BTN_SECONDARY }} tw-mt-3 !tw-min-h-9 tw-w-full sm:tw-w-auto">
                                        <i class="fas fa-plus" aria-hidden="true"></i>{{ __('vehicle.routes.add_stop') }}
                                    </button>
                                @endif
                                <p class="{{ $ui::HINT }} tw-break-words">{{ __('vehicle.routes.name_preview') }}:
                                    <span class="tw-font-semibold tw-text-slate-800" x-text="($wire.stops || []).map((s) => String(s ?? '').trim()).filter(Boolean).join(' → ') || '—'"></span>
                                </p>
                                @error('name') <p class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>
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
