{{--
    Date field with its own calendar (Tailwind + Alpine, inside `.tw-root`).

    The field shows the chosen date written out in the page language (weekday, day, month,
    year). Pressing it opens a large month calendar; choosing a day sets the value and
    closes the calendar. "Today" and "yesterday" shortcuts sit under the grid.
    The Livewire property holds YYYY-MM-DD.

    <x-ui.date-field id="lot-date" wire:model="entryDate" :invalid="$errors->has('entryDate')" />

    Optional, for filters (all off by default, so existing fields are unchanged):
      compact      short date text ("১৩ নভে ২০২৬"), no chevron — fits a narrow field
      clearable    a "clear" button in the calendar empties the value
      placeholder  text shown while empty
      min / max    YYYY-MM-DD; days outside the range cannot be chosen
      pop-width    smallest calendar width in px (240)

    Without Livewire (a plain Blade page): leave out wire:model. The chosen date is then kept in
    a hidden input with the id "{id}-value" (and the given `name`), which fires a normal
    `change` event whenever the date changes:
      <x-ui.date-field id="report-from" name="date_from" value="2026-11-13" compact clearable />

    Keyboard: Enter/Space opens; arrow keys move by day/week; PageUp/PageDown change month;
    Enter chooses; Escape closes.
--}}
@props(['id', 'invalid' => false, 'compact' => false, 'clearable' => false, 'placeholder' => null, 'min' => null, 'max' => null, 'popWidth' => 240, 'name' => null, 'value' => ''])

@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $locale = app()->getLocale() === 'bn' ? 'bn-BD' : 'en-GB';
    // Bound to a Livewire property, or standing alone with a hidden input
    $wired = (bool) $attributes->wire('model')->value();
@endphp

{{-- min / max live in data attributes so they follow Livewire updates without rebuilding the field --}}
<div class="tw-relative" data-min="{{ $min }}" data-max="{{ $max }}"
    x-data="{
        value: @if ($wired) @entangle($attributes->wire('model')) @else @js((string) $value) @endif,
        locale: @js($locale),
        compact: @js((bool) $compact),
        minWidth: @js((int) $popWidth),
        open: false,
        popStyle: '',
        onScroll: null,
        // The calendar floats above the page (position: fixed) at the field's width, so it
        // never adds a scrollbar to the modal; it opens upwards when there is more room there.
        place() {
            const rect = this.$refs.button.getBoundingClientRect();
            const gap = 4, margin = 8;
            const width = Math.max(rect.width, this.minWidth);
            const height = this.$refs.pop.offsetHeight || (width + 110);
            const below = window.innerHeight - rect.bottom - margin - gap;
            const above = rect.top - margin - gap;
            const up = below < height && above > below;
            const left = Math.max(margin, Math.min(rect.left, window.innerWidth - width - margin));
            this.popStyle = 'position:fixed;left:' + left + 'px;width:' + width + 'px;'
                + (up ? 'bottom:' + (window.innerHeight - rect.top + gap) + 'px;' : 'top:' + (rect.bottom + gap) + 'px;');
        },
        year: 0,
        month: 0,
        focusIso: '',
        pad(n) { return String(n).padStart(2, '0'); },
        iso(date) { return date.getFullYear() + '-' + this.pad(date.getMonth() + 1) + '-' + this.pad(date.getDate()); },
        parse(iso) {
            if (! /^\d{4}-\d{2}-\d{2}$/.test(iso || '')) return null;
            const [y, m, d] = iso.split('-').map(Number);
            return new Date(y, m - 1, d);
        },
        offset(days) { const d = new Date(); d.setDate(d.getDate() + days); return this.iso(d); },
        number(n) { return new Intl.NumberFormat(this.locale, { useGrouping: false }).format(n); },
        get label() {
            const date = this.parse(this.value);
            if (! date) return '';
            return new Intl.DateTimeFormat(this.locale, this.compact
                ? { day: 'numeric', month: 'short', year: 'numeric' }
                : { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(date);
        },
        // ISO dates compare correctly as text.
        allowed(iso) {
            const min = this.$root.dataset.min, max = this.$root.dataset.max;
            return (! min || iso >= min) && (! max || iso <= max);
        },
        get monthLabel() {
            return new Intl.DateTimeFormat(this.locale, { month: 'long', year: 'numeric' }).format(new Date(this.year, this.month, 1));
        },
        get weekdays() {
            // 2023-01-01 was a Sunday.
            return [...Array(7).keys()].map(i => new Intl.DateTimeFormat(this.locale, { weekday: 'short' }).format(new Date(2023, 0, 1 + i)));
        },
        get days() {
            const first = new Date(this.year, this.month, 1);
            const start = new Date(this.year, this.month, 1 - first.getDay());
            const today = this.iso(new Date());
            return [...Array(42).keys()].map(i => {
                const date = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                const iso = this.iso(date);
                return {
                    iso,
                    text: this.number(date.getDate()),
                    full: new Intl.DateTimeFormat(this.locale, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(date),
                    inMonth: date.getMonth() === this.month,
                    today: iso === today,
                    selected: iso === this.value,
                    allowed: this.allowed(iso)
                };
            });
        },
        show() {
            const date = this.parse(this.value) || new Date();
            this.year = date.getFullYear();
            this.month = date.getMonth();
            this.focusIso = this.iso(date);
            this.place();
            this.open = true;
            this.$nextTick(() => this.place());
            this.onScroll = (event) => { if (! this.$refs.pop.contains(event.target)) this.place(); };
            window.addEventListener('scroll', this.onScroll, true);
            window.addEventListener('resize', this.onScroll);
            this.focusDay();
        },
        close(refocus = true) {
            if (! this.open) return;
            this.open = false;
            window.removeEventListener('scroll', this.onScroll, true);
            window.removeEventListener('resize', this.onScroll);
            if (refocus) this.$nextTick(() => this.$refs.button.focus());
        },
        toggle() { this.open ? this.close() : this.show(); },
        choose(iso) { if (iso !== '' && ! this.allowed(iso)) return; this.value = iso; this.close(); },
        shiftMonth(step) {
            const date = new Date(this.year, this.month + step, 1);
            this.year = date.getFullYear();
            this.month = date.getMonth();
        },
        moveFocus(days) {
            const date = this.parse(this.focusIso) || new Date();
            date.setDate(date.getDate() + days);
            this.focusIso = this.iso(date);
            this.year = date.getFullYear();
            this.month = date.getMonth();
            this.focusDay();
        },
        moveMonth(step) {
            const date = this.parse(this.focusIso) || new Date();
            date.setMonth(date.getMonth() + step);
            this.focusIso = this.iso(date);
            this.year = date.getFullYear();
            this.month = date.getMonth();
            this.focusDay();
        },
        focusDay() {
            this.$nextTick(() => {
                const el = this.$refs.grid && this.$refs.grid.querySelector('[data-iso=\'' + this.focusIso + '\']');
                if (el) el.focus();
            });
        }
    }"
    @unless ($wired)
        x-init="$watch('value', v => { $refs.plain.value = v || ''; $refs.plain.dispatchEvent(new Event('change', { bubbles: true })); })"
    @endunless
    x-on:keydown.escape.stop="close()"
    x-on:click.outside="close(false)">

    @unless ($wired)
        <input type="hidden" x-ref="plain" id="{{ $id }}-value" @if ($name) name="{{ $name }}" @endif value="{{ $value }}">
    @endunless

    <button type="button" id="{{ $id }}" x-ref="button" x-on:click="toggle()"
        aria-haspopup="dialog" x-bind:aria-expanded="open.toString()" aria-controls="{{ $id }}-calendar"
        @if ($invalid) aria-invalid="true" @endif
        class="{{ $ui::INPUT }} tw-flex tw-items-center tw-gap-2.5 tw-text-left {{ $invalid ? $ui::INPUT_ERROR : '' }}">
        <span class="tw-flex tw-h-6 tw-w-6 tw-flex-none tw-items-center tw-justify-center tw-rounded-md tw-bg-brand-50 tw-text-brand-600" aria-hidden="true">
            <i class="fas fa-calendar-alt tw-text-xs"></i>
        </span>
        <span class="tw-min-w-0 tw-flex-1 tw-truncate tw-font-medium" x-bind:class="label ? '' : 'tw-font-normal tw-text-slate-400'"
            x-text="label || @js($placeholder ?: __('commontable.select_date'))"></span>
        @unless ($compact)
            <i class="fas fa-chevron-down tw-text-xs tw-text-slate-400 tw-transition" x-bind:class="open ? 'tw-rotate-180' : ''" aria-hidden="true"></i>
        @endunless
    </button>

    {{-- Calendar --}}
    <div id="{{ $id }}-calendar" x-show="open" x-cloak x-ref="pop" x-bind:style="popStyle" x-transition.opacity.duration.100ms
        role="dialog" aria-label="{{ __('commontable.select_date') }}"
        class="tw-z-[1070] tw-rounded-xl tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-p-2 tw-shadow-dialog">
        {{-- The calendar is as wide as the field (never narrower than 15rem); its day cells are square and scale with it. --}}

        <div class="tw-mb-2 tw-flex tw-items-center tw-justify-between tw-gap-2">
            <button type="button" x-on:click="shiftMonth(-1)" aria-label="{{ __('commontable.previous_month') }}" title="{{ __('commontable.previous_month') }}"
                class="tw-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-border-0 tw-bg-transparent tw-text-slate-600 tw-transition hover:tw-bg-slate-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40">
                <i class="fas fa-chevron-left tw-text-sm" aria-hidden="true"></i>
            </button>
            <p class="tw-m-0 tw-text-[15px] tw-font-bold tw-text-slate-900" aria-live="polite" x-text="monthLabel"></p>
            <button type="button" x-on:click="shiftMonth(1)" aria-label="{{ __('commontable.next_month') }}" title="{{ __('commontable.next_month') }}"
                class="tw-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-border-0 tw-bg-transparent tw-text-slate-600 tw-transition hover:tw-bg-slate-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40">
                <i class="fas fa-chevron-right tw-text-sm" aria-hidden="true"></i>
            </button>
        </div>

        <div class="tw-grid tw-grid-cols-7 tw-gap-0.5 tw-text-center tw-text-[11px] tw-font-semibold tw-text-slate-500" aria-hidden="true">
            <template x-for="name in weekdays" x-bind:key="name">
                <span class="tw-truncate tw-py-1" x-text="name"></span>
            </template>
        </div>

        <div x-ref="grid" class="tw-grid tw-grid-cols-7 tw-gap-0.5"
            x-on:keydown.arrow-left.prevent="moveFocus(-1)" x-on:keydown.arrow-right.prevent="moveFocus(1)"
            x-on:keydown.arrow-up.prevent="moveFocus(-7)" x-on:keydown.arrow-down.prevent="moveFocus(7)"
            x-on:keydown.page-up.prevent="moveMonth(-1)" x-on:keydown.page-down.prevent="moveMonth(1)">
            <template x-for="day in days" x-bind:key="day.iso">
                <button type="button" x-on:click="choose(day.iso)" x-bind:data-iso="day.iso"
                    x-bind:tabindex="day.iso === focusIso ? 0 : -1"
                    x-bind:aria-label="day.full" x-bind:aria-pressed="day.selected.toString()"
                    x-bind:aria-current="day.today ? 'date' : null"
                    x-bind:disabled="! day.allowed"
                    x-bind:class="{
                        'tw-bg-brand-600 tw-text-white tw-font-bold hover:tw-bg-brand-700': day.selected,
                        'tw-bg-transparent tw-text-slate-900 hover:tw-bg-brand-50': ! day.selected && day.inMonth && day.allowed,
                        'tw-bg-transparent tw-text-slate-300 hover:tw-bg-slate-50': ! day.selected && ! day.inMonth && day.allowed,
                        'tw-cursor-not-allowed tw-bg-transparent tw-text-slate-300 tw-line-through': ! day.selected && ! day.allowed,
                        'tw-ring-1 tw-ring-inset tw-ring-brand-500 tw-font-bold': day.today && ! day.selected
                    }"
                    class="tw-flex tw-aspect-square tw-min-h-8 tw-w-full tw-items-center tw-justify-center tw-rounded-lg tw-border-0 tw-p-0 tw-text-sm tw-tabular-nums tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500"
                    x-text="day.text"></button>
            </template>
        </div>

        <div class="tw-mt-2 tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-border-0 tw-border-t tw-border-solid tw-border-slate-100 tw-pt-2">
            @foreach ([0 => __('commontable.today'), -1 => __('commontable.yesterday')] as $days => $text)
                <button type="button" x-on:click="choose(offset({{ $days }}))" x-bind:disabled="! allowed(offset({{ $days }}))"
                    x-bind:class="value === offset({{ $days }}) ? 'tw-border-brand-600 tw-bg-brand-600 tw-text-white' : 'tw-border-slate-300 tw-bg-white tw-text-slate-700 hover:tw-bg-slate-50'"
                    class="tw-min-h-8 tw-rounded-full tw-border tw-border-solid tw-px-3 tw-text-xs tw-font-semibold tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40 disabled:tw-cursor-not-allowed disabled:tw-opacity-40">
                    {{ $text }}
                </button>
            @endforeach
            @if ($clearable)
                <button type="button" x-show="value" x-on:click="choose('')"
                    class="tw-ml-auto tw-min-h-8 tw-rounded-full tw-border tw-border-solid tw-border-transparent tw-bg-transparent tw-px-3 tw-text-xs tw-font-semibold tw-text-rose-600 tw-transition hover:tw-bg-rose-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-rose-500/40">
                    <i class="fas fa-times tw-mr-1" aria-hidden="true"></i>{{ __('commontable.clear_date') }}
                </button>
            @endif
        </div>
    </div>
</div>
