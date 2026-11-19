@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $isBn = app()->getLocale() === 'bn';
    $fileUrl = fn ($driverId, $kind) => route('vehicle-drivers.file', ['driver' => $driverId, 'kind' => $kind]);
@endphp

<div x-data x-on:app-toast.window="(window.appToast || window.toastr)[$event.detail.type]($event.detail.message)">
    @include('livewire.vehicles.partials.page-top')

    <div class="content px-3">
        <div class="card card-primary">
            @include('livewire.vehicles.partials.card-head')
            <div class="card-body position-relative">
                @include('livewire.partials.toolbar', ['id' => 'vdriver', 'placeholder' => $t('search')])
                @include('livewire.vehicles.partials.loading')

                <div class="table-responsive">
                    <table class="table crud-table" id="vehicle-drivers-table">
                        <thead>
                            <tr>
                                <th class="crud-serial">{{ __('commontable.serial') }}</th>
                                <th>{{ $f('photo') }}</th>
                                <th>{{ $f('name_bn') }}</th>
                                <th>{{ $f('mobile') }}</th>
                                <th>{{ $f('email') }}</th>
                                <th>{{ $f('license_no') }}</th>
                                <th>{{ __('vehicle.types.vehicles') }}</th>
                                <th class="crud-actions">{{ __('commontable.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr wire:key="vdriver-{{ $row->id }}">
                                    <td class="crud-serial">{{ $digits($rows->firstItem() + $loop->index) }}</td>
                                    <td class="text-center">
                                        @if ($row->photo_path)
                                            <img src="{{ $fileUrl($row->id, 'photo') }}" alt="" loading="lazy"
                                                style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #e2e8f0;">
                                        @else
                                            <span class="d-inline-flex align-items-center justify-content-center text-muted"
                                                style="width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9;" title="{{ $t('no_photo') }}">
                                                <i class="fas fa-user" aria-hidden="true"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="crud-wrap">
                                        <span class="font-weight-bold">{{ $row->name_bn ?: $row->name }}</span>
                                        @if ($row->name && $row->name !== $row->name_bn)<br><small class="text-muted">{{ $row->name }}</small>@endif
                                    </td>
                                    <td class="text-center">{{ $row->mobile ? $digits($row->mobile) : __('vehicle.none') }}</td>
                                    <td class="text-center crud-wrap">{{ $row->email ?: __('vehicle.none') }}</td>
                                    <td class="text-center">{{ $row->license_no ?: __('vehicle.none') }}</td>
                                    <td class="text-center">{{ $digits($row->vehicles_count) }}{{ $isBn ? 'টি' : '' }}</td>
                                    @include('livewire.vehicles.partials.row-actions', ['id' => $row->id, 'label' => $row->name_bn ?: $row->name, 'view' => true])
                                </tr>
                            @empty
                                <tr wire:key="vdriver-empty">
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
            <x-ui.modal wire:model="showForm" max-width="3xl" labelledby="vdriver-form-title">
                <form wire:submit="save" class="tw-m-0 tw-flex tw-min-h-0 tw-flex-1 tw-flex-col" novalidate>
                    @include('livewire.vehicles.partials.modal-open', ['titleId' => 'vdriver-form-title'])
                    <div class="{{ $ui::MODAL_BODY }}">
                        @error('form') <div class="{{ $ui::FORM_ALERT }}" role="alert"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i><span>{{ $message }}</span></div> @enderror
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2">
                            @include('livewire.vehicles.partials.field', ['name' => 'nameBn', 'label' => $f('name_bn'), 'required' => true, 'max' => 150, 'lang' => 'bn'])
                            @include('livewire.vehicles.partials.field', ['name' => 'name', 'label' => $f('name_en'), 'required' => true, 'max' => 150])
                            @include('livewire.vehicles.partials.field', ['name' => 'mobile', 'label' => $f('mobile'), 'type' => 'tel', 'required' => true, 'max' => 17])
                            @include('livewire.vehicles.partials.field', ['name' => 'email', 'label' => $f('email'), 'type' => 'email', 'required' => true, 'max' => 191, 'hint' => $t('account_note')])

                            {{-- Photo --}}
                            <div>
                                <label for="vf-photo" class="{{ $ui::LABEL }}">{{ $f('photo') }}</label>
                                <div class="tw-flex tw-items-center tw-gap-3">
                                    <span class="tw-inline-flex tw-h-16 tw-w-16 tw-flex-none tw-items-center tw-justify-center tw-overflow-hidden tw-rounded-full tw-border tw-border-solid tw-border-slate-200 tw-bg-slate-50 tw-text-slate-400">
                                        @if ($photo && method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                                            <img src="{{ $photo->temporaryUrl() }}" alt="" class="tw-h-full tw-w-full tw-object-cover">
                                        @elseif ($hasPhoto && $editingId)
                                            <img src="{{ $fileUrl($editingId, 'photo') }}?v={{ now()->timestamp }}" alt="" class="tw-h-full tw-w-full tw-object-cover">
                                        @else
                                            <i class="fas fa-user tw-text-xl" aria-hidden="true"></i>
                                        @endif
                                    </span>
                                    <input id="vf-photo" type="file" accept="image/png,image/jpeg,image/webp" wire:model="photo"
                                        class="tw-block tw-w-full tw-text-sm tw-text-slate-600 file:tw-mr-3 file:tw-rounded-lg file:tw-border-0 file:tw-bg-brand-50 file:tw-px-3 file:tw-py-2 file:tw-font-semibold file:tw-text-brand-700">
                                </div>
                                <div wire:loading wire:target="photo" class="{{ $ui::HINT }}"><i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> {{ __('commontable.loading') }}</div>
                                @error('photo') <p class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                                <p class="{{ $ui::HINT }}">{{ $t('photo_hint') }}</p>
                            </div>

                            {{-- Licence image: the number is read from it in the browser --}}
                            {{-- Reads the licence number from the image in the browser (Tesseract.js, loaded the first
                                 time it is needed) and puts it in the licence number field, where it can be corrected. --}}
                            <div x-data="{
                                busy: false, found: false, status: '',
                                texts: @js(['reading' => $t('reading'), 'done' => $t('read_done'), 'none' => $t('read_none')]),
                                load() {
                                    if (window.Tesseract) return Promise.resolve();
                                    return window.__ocrLoading = window.__ocrLoading || new Promise((resolve, reject) => {
                                        const s = document.createElement('script');
                                        s.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/tesseract.min.js';
                                        s.onload = resolve; s.onerror = reject;
                                        document.head.appendChild(s);
                                    });
                                },
                                pick(raw) {
                                    const text = String(raw || '').toUpperCase().replace(/[|]/g, 'I');
                                    // Smart card licence: 2 letters, 7 digits, 1 letter, 5 digits (e.g. DK0123456L00001);
                                    // OCR may put dots / dashes between the parts and read 0 as O or 1 as I
                                    const digits = (v) => v.replace(/O/g, '0').replace(/[IL]/g, '1');
                                    let m = text.match(/\b([A-Z]{2})[\s.,:\-]*([0-9OI]{7})[\s.,:\-]*([A-Z])[\s.,:\-]*([0-9OIL]{5})\b/);
                                    if (m) return m[1] + digits(m[2]) + m[3] + digits(m[4]);
                                    m = text.match(/(?:LICEN[CS]E|D\.?\s?L\.?)\s*(?:NO|NUMBER|NUM)\.?\s*[:\-]?\s*([A-Z0-9][A-Z0-9\-\/ ]{5,24})/);
                                    if (m) return m[1].replace(/\s+/g, '').replace(/[^A-Z0-9\-\/]+$/, '');
                                    const words = text.split(/\s+/).filter((w) => /^(?=.*\d)(?=.*[A-Z])[A-Z0-9\-\/]{8,20}$/.test(w));
                                    return words.sort((a, b) => b.length - a.length)[0] || '';
                                },
                                async read(event) {
                                    const file = event.target.files && event.target.files[0];
                                    if (!file) return;
                                    this.busy = true; this.found = false; this.status = this.texts.reading;
                                    try {
                                        await this.load();
                                        const result = await window.Tesseract.recognize(file, 'eng');
                                        const number = this.pick(result && result.data ? result.data.text : '');
                                        if (number) { $wire.licenseNo = number; this.found = true; this.status = this.texts.done; }
                                        else { this.status = this.texts.none; }
                                    } catch (e) { this.status = this.texts.none; }
                                    finally { this.busy = false; }
                                },
                            }">
                                <label for="vf-license" class="{{ $ui::LABEL }}">{{ $f('license_image') }}</label>
                                <div class="tw-flex tw-items-center tw-gap-3">
                                    <span class="tw-inline-flex tw-h-16 tw-w-24 tw-flex-none tw-items-center tw-justify-center tw-overflow-hidden tw-rounded-lg tw-border tw-border-solid tw-border-slate-200 tw-bg-slate-50 tw-text-slate-400">
                                        @if ($license && method_exists($license, 'isPreviewable') && $license->isPreviewable())
                                            <img src="{{ $license->temporaryUrl() }}" alt="" class="tw-h-full tw-w-full tw-object-cover">
                                        @elseif ($hasLicense && $editingId)
                                            <img src="{{ $fileUrl($editingId, 'license') }}?v={{ now()->timestamp }}" alt="" class="tw-h-full tw-w-full tw-object-cover">
                                        @else
                                            <i class="fas fa-id-card tw-text-xl" aria-hidden="true"></i>
                                        @endif
                                    </span>
                                    <input id="vf-license" type="file" accept="image/png,image/jpeg,image/webp" wire:model="license"
                                        x-on:change="read($event)"
                                        class="tw-block tw-w-full tw-text-sm tw-text-slate-600 file:tw-mr-3 file:tw-rounded-lg file:tw-border-0 file:tw-bg-brand-50 file:tw-px-3 file:tw-py-2 file:tw-font-semibold file:tw-text-brand-700">
                                </div>
                                <p class="{{ $ui::HINT }}" x-show="status" x-cloak>
                                    <i class="fas" :class="busy ? 'fa-circle-notch fa-spin' : (found ? 'fa-check-circle tw-text-emerald-600' : 'fa-info-circle tw-text-amber-600')" aria-hidden="true"></i>
                                    <span x-text="status"></span>
                                </p>
                                @error('license') <p class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
                            </div>

                            @include('livewire.vehicles.partials.field', ['name' => 'licenseNo', 'label' => $f('license_no'), 'max' => 50, 'wide' => true, 'hint' => $t('license_hint')])
                        </div>
                    </div>
                    @include('livewire.vehicles.partials.modal-foot')
                </form>
            </x-ui.modal>

            {{-- ============ Details ============ --}}
            <x-ui.modal wire:model="showView" max-width="2xl" labelledby="vdriver-view-title">
                @if ($driver = $this->viewing)
                    <div class="{{ $ui::MODAL_HEADER }}">
                        <div class="tw-min-w-0 tw-flex-1">
                            <h2 id="vdriver-view-title" class="{{ $ui::MODAL_TITLE }}">{{ $t('view') }}</h2>
                            <p class="{{ $ui::MODAL_HINT }}">{{ $driver->name_bn }} @if ($driver->name) · {{ $driver->name }} @endif</p>
                        </div>
                        <x-ui.modal-today />
                        <button type="button" x-on:click="open = false" class="{{ $ui::MODAL_CLOSE }}" aria-label="{{ __('commontable.close') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="{{ $ui::MODAL_BODY }}">
                        <div class="tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row">
                            <div class="tw-flex tw-h-32 tw-w-32 tw-flex-none tw-items-center tw-justify-center tw-self-center tw-overflow-hidden tw-rounded-2xl tw-border tw-border-solid tw-border-slate-200 tw-bg-slate-50 tw-text-slate-300 sm:tw-self-start">
                                @if ($driver->photo_path)
                                    <img src="{{ $fileUrl($driver->id, 'photo') }}" alt="" class="tw-h-full tw-w-full tw-object-cover">
                                @else
                                    <i class="fas fa-user tw-text-4xl" aria-hidden="true"></i>
                                @endif
                            </div>
                            <dl class="tw-m-0 tw-grid tw-flex-1 tw-grid-cols-1 tw-gap-x-6 tw-gap-y-3 sm:tw-grid-cols-2">
                                @foreach ([
                                    $f('mobile') => $driver->mobile ? $digits($driver->mobile) : __('vehicle.none'),
                                    $f('email') => $driver->email ?: __('vehicle.none'),
                                    $f('license_no') => $driver->license_no ?: __('vehicle.none'),
                                    $t('account') => $driver->user && $driver->user->is_active ? $t('account_note') : __('vehicle.none'),
                                ] as $term => $value)
                                    <div class="tw-min-w-0">
                                        <dt class="tw-text-xs tw-font-semibold tw-text-slate-500">{{ $term }}</dt>
                                        <dd class="tw-m-0 tw-break-words tw-text-sm tw-font-semibold tw-text-slate-900">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                        @if ($driver->license_path)
                            <div>
                                <p class="{{ $ui::LABEL }}">{{ $f('license_image') }}</p>
                                <a href="{{ $fileUrl($driver->id, 'license') }}" target="_blank" rel="noopener">
                                    <img src="{{ $fileUrl($driver->id, 'license') }}" alt="{{ $f('license_image') }}" class="tw-max-h-64 tw-max-w-full tw-rounded-xl tw-border tw-border-solid tw-border-slate-200">
                                </a>
                            </div>
                        @endif
                        @if ($driver->vehicles->count())
                            <div>
                                <p class="{{ $ui::LABEL }}">{{ __('vehicle.types.vehicles') }}</p>
                                <ul class="tw-m-0 tw-flex tw-list-none tw-flex-wrap tw-gap-2 tw-p-0">
                                    @foreach ($driver->vehicles as $vehicle)
                                        <li class="tw-rounded-full tw-bg-brand-50 tw-px-3 tw-py-1 tw-text-sm tw-font-semibold tw-text-brand-700">
                                            {{ $vehicle->vehicle_no }}@if ($vehicle->type) · {{ $vehicle->type->label }}@endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="{{ $ui::MODAL_FOOTER }}">
                        <button type="button" x-on:click="open = false" class="{{ $ui::BTN_SECONDARY }}">{{ __('commontable.close') }}</button>
                    </div>
                @endif
            </x-ui.modal>

            <x-ui.confirm-delete wire:model="showDelete" action="delete" id="vdriver-delete"
                :title="__('vehicle.delete_title')" :text="__('vehicle.delete_text')" :name="$deleteLabel" />
        </div>
    </div>

</div>
