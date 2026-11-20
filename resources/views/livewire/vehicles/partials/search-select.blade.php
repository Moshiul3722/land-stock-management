{{--
    A drop-down with a search box (like select2), for long lists in a vehicle management modal.
      $name     Livewire property (holds the chosen value as a string; '' = nothing chosen)
      $label    label text
      $options  [value => label]
      $required bool
      $hint     small help text under the field
      $wide     true: spans both columns
      $live     true: the choice is sent to the server at once (e.g. when other fields depend on it)
      $disabled [value => note]: shown greyed with the note, and cannot be chosen
--}}
@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $fid = 'vf-' . \Illuminate\Support\Str::kebab($name);
    $hasError = $errors->has($name);
    $disabled = $disabled ?? [];
    $list = collect($options ?? [])->map(fn ($text, $value) => ['value' => (string) $value, 'label' => (string) $text, 'note' => isset($disabled[$value]) ? (string) $disabled[$value] : null])->values();
@endphp
<div x-data="{
        open: false,
        q: '',
        active: 0,
        value: $wire.entangle('{{ $name }}'){{ ! empty($live) ? '.live' : '' }},
        options: @js($list),
        get chosen() { return this.options.find((o) => o.value === String(this.value ?? '')) },
        get filtered() {
            const q = this.q.trim().toLowerCase();
            return q ? this.options.filter((o) => o.label.toLowerCase().includes(q)) : this.options;
        },
        style: '',
        listHeight: 240,
        // The list floats over the dialog (fixed position), under the field or above it when
        // there is more room there, and follows the field while the dialog scrolls.
        place() {
            const r = this.$refs.button.getBoundingClientRect();
            const below = window.innerHeight - r.bottom - 12;
            const above = r.top - 12;
            const up = below < 260 && above > below;
            this.listHeight = Math.max(120, Math.min(240, (up ? above : below) - 64));
            this.style = `left:${r.left}px;width:${r.width}px;` + (up ? `bottom:${window.innerHeight - r.top + 4}px;` : `top:${r.bottom + 4}px;`);
        },
        follow: null,
        show() {
            this.place();
            this.open = true; this.q = ''; this.active = 0;
            this.follow = () => this.open && this.place();
            window.addEventListener('scroll', this.follow, true);
            window.addEventListener('resize', this.follow);
            this.$nextTick(() => this.$refs.search.focus());
        },
        close() {
            this.open = false;
            if (this.follow) { window.removeEventListener('scroll', this.follow, true); window.removeEventListener('resize', this.follow); }
        },
        pick(option) { if (option && option.note) return; this.value = option ? option.value : ''; this.close(); this.$refs.button.focus(); },
        move(step) {
            const count = this.filtered.length;
            if (!count) return;
            this.active = (this.active + step + count) % count;
            this.$nextTick(() => { const el = this.$refs.list.querySelector('[data-active=true]'); if (el) el.scrollIntoView({ block: 'nearest' }); });
        },
    }" x-on:click.outside="close()" x-on:keydown.escape.stop="if (open) { close(); $refs.button.focus(); }" class="tw-relative {{ ! empty($wide) ? 'sm:tw-col-span-2' : '' }}">
    <label for="{{ $fid }}" class="{{ $ui::LABEL }}">{{ $label }} @if (! empty($required))<span class="tw-text-rose-600">*</span>@endif</label>
    <button type="button" id="{{ $fid }}" x-ref="button" x-on:click="open ? close() : show()"
        x-on:keydown.arrow-down.prevent="show()" aria-haspopup="listbox" x-bind:aria-expanded="open"
        class="{{ $ui::INPUT }} tw-flex tw-items-center tw-justify-between tw-gap-2 tw-text-left @if ($hasError) {{ $ui::INPUT_ERROR }} @endif"
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $fid }}-error" @endif>
        <span class="tw-min-w-0 tw-truncate" x-bind:class="chosen ? 'tw-text-slate-900' : 'tw-text-slate-400'"
            x-text="chosen ? chosen.label : @js('— ' . __('vehicle.choose') . ' —')"></span>
        <i class="fas fa-chevron-down tw-flex-none tw-text-xs tw-text-slate-400 tw-transition" x-bind:class="open && 'tw-rotate-180'" aria-hidden="true"></i>
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.100ms x-bind:style="style"
        class="tw-fixed tw-z-[1070] tw-overflow-hidden tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-shadow-lg">
        <div class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-p-2">
            <div class="tw-relative">
                <i class="fas fa-search tw-pointer-events-none tw-absolute tw-left-3 tw-top-1/2 tw--translate-y-1/2 tw-text-xs tw-text-slate-400" aria-hidden="true"></i>
                <input type="search" x-ref="search" x-model="q" x-on:input="active = 0" autocomplete="off"
                    x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)"
                    x-on:keydown.enter.prevent="filtered[active] && pick(filtered[active])"
                    placeholder="{{ __('commontable.search') }}" aria-label="{{ __('commontable.search') }}: {{ $label }}"
                    class="{{ $ui::INPUT }} !tw-min-h-9 tw-pl-8">
            </div>
        </div>
        <ul x-ref="list" role="listbox" x-bind:style="`max-height:${listHeight}px`" class="tw-m-0 tw-list-none tw-overflow-y-auto tw-p-1">
            <li role="option" x-on:click="pick(null)" x-bind:aria-selected="!chosen"
                class="tw-cursor-pointer tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-text-slate-400 hover:tw-bg-slate-50">— {{ __('vehicle.choose') }} —</li>
            <template x-for="(option, index) in filtered" x-bind:key="option.value">
                <li role="option" x-on:click="pick(option)" x-on:mouseenter="active = index"
                    x-bind:data-active="index === active" x-bind:aria-selected="chosen && chosen.value === option.value" x-bind:aria-disabled="option.note ? 'true' : null"
                    x-bind:class="[option.note ? 'tw-cursor-not-allowed tw-text-slate-400' : 'tw-cursor-pointer', !option.note && index === active ? 'tw-bg-brand-50' : '', chosen && chosen.value === option.value ? 'tw-font-semibold tw-text-brand-700' : (option.note ? '' : 'tw-text-slate-700')]"
                    class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-rounded-md tw-px-3 tw-py-2 tw-text-sm">
                    <span class="tw-min-w-0">
                        <span class="tw-block" x-text="option.label"></span>
                        <span class="tw-block tw-text-xs tw-text-amber-700" x-show="option.note" x-text="option.note"></span>
                    </span>
                    <i class="fas fa-check tw-text-xs tw-text-brand-600" x-show="chosen && chosen.value === option.value" aria-hidden="true"></i>
                </li>
            </template>
            <li x-show="filtered.length === 0" class="tw-px-3 tw-py-2 tw-text-sm tw-text-slate-400">{{ __('commontable.dt_zero_records') }}</li>
        </ul>
    </div>
    @error($name) <p id="{{ $fid }}-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
    @if (! empty($hint)) <p class="{{ $ui::HINT }}">{{ $hint }}</p> @endif
</div>
