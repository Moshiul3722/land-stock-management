{{--
    Searchable dropdown (combobox) for the Livewire / Tailwind forms — the Select2 equivalent
    for these modals. Must sit inside `.tw-root`.

    <x-ui.search-select id="x" wire:model.live="categoryId" :options="$options"
        :placeholder="..." :search-placeholder="..." :empty-text="..." />

    $options: list of ['value' => ..., 'label' => ...].
    - Type to filter; Up/Down/Enter/Escape work from the keyboard.
    - Ten options are visible at a time; the rest scroll.
    - `wire:model.live` sends the choice at once (use it when another field depends on it).
--}}
@props([
    'id',
    'options' => [],
    'placeholder' => '',
    'searchPlaceholder' => '',
    'emptyText' => '',
    'clearLabel' => '',
    'disabled' => false,
    'invalid' => false,
    'clearable' => true,
])

@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $options = collect($options)->map(fn ($option) => ['value' => (string) $option['value'], 'label' => (string) $option['label']])->values();
@endphp

{{-- The key changes with the option list, so Alpine starts afresh when the options change. --}}
<div wire:key="{{ $id }}-{{ md5($options->toJson()) }}-{{ $disabled ? 'off' : 'on' }}"
    x-data="{
        value: @entangle($attributes->wire('model')),
        options: @js($options),
        open: false,
        query: '',
        active: 0,
        popStyle: '',
        listMax: 360,
        onScroll: null,
        // The dropdown floats above the page (position: fixed), so opening it never makes
        // the modal taller or adds a scrollbar. It opens upwards when there is more room there.
        place() {
            const rect = this.$refs.button.getBoundingClientRect();
            const gap = 4, margin = 8, chrome = 64; // chrome = search box + padding
            const below = window.innerHeight - rect.bottom - margin - gap;
            const above = rect.top - margin - gap;
            const up = below < 220 && above > below;
            const room = Math.max(120, (up ? above : below) - chrome);
            this.listMax = Math.min(360, room);
            const width = Math.max(rect.width, 200);
            const left = Math.max(margin, Math.min(rect.left, window.innerWidth - width - margin));
            this.popStyle = 'position:fixed;left:' + left + 'px;width:' + width + 'px;'
                + (up ? 'bottom:' + (window.innerHeight - rect.top + gap) + 'px;' : 'top:' + (rect.bottom + gap) + 'px;');
        },
        get selected() { return this.options.find(o => o.value === String(this.value ?? '')) || null; },
        get filtered() {
            const q = this.query.trim().toLowerCase();
            return q === '' ? this.options : this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        toggle() { this.open ? this.close() : this.show(); },
        show() {
            if (@js((bool) $disabled)) return;
            this.place();
            this.open = true;
            this.onScroll = (event) => { if (! this.$refs.pop.contains(event.target)) this.place(); };
            window.addEventListener('scroll', this.onScroll, true);
            window.addEventListener('resize', this.onScroll);
            this.query = '';
            this.active = Math.max(0, this.filtered.findIndex(o => o.value === String(this.value ?? '')));
            this.$nextTick(() => { this.$refs.search.focus(); this.scrollToActive(); });
        },
        close(focusButton = true) {
            if (! this.open) return;
            this.open = false;
            window.removeEventListener('scroll', this.onScroll, true);
            window.removeEventListener('resize', this.onScroll);
            if (focusButton) this.$nextTick(() => this.$refs.button.focus());
        },
        choose(option) { this.value = option.value; this.close(); },
        clear() { this.value = null; this.close(); },
        move(step) {
            const count = this.filtered.length;
            if (count === 0) return;
            this.active = (this.active + step + count) % count;
            this.scrollToActive();
        },
        scrollToActive() {
            this.$nextTick(() => {
                const el = this.$refs.list && this.$refs.list.querySelector('[data-active=true]');
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },
        enter() { const option = this.filtered[this.active]; if (option) this.choose(option); }
    }"
    x-on:keydown.escape.stop="close()"
    x-on:click.outside="close(false)"
    class="tw-relative">

    <button type="button" id="{{ $id }}" x-ref="button" x-on:click="toggle()"
        x-on:keydown.arrow-down.prevent="show()" x-on:keydown.arrow-up.prevent="show()"
        aria-haspopup="listbox" x-bind:aria-expanded="open.toString()" aria-controls="{{ $id }}-list"
        @disabled($disabled) @if ($invalid) aria-invalid="true" @endif
        class="{{ $ui::INPUT }} tw-flex tw-items-center tw-justify-between tw-gap-2 tw-text-left {{ $invalid ? $ui::INPUT_ERROR : '' }}">
        <span class="tw-min-w-0 tw-flex-1 tw-truncate" x-bind:class="selected ? '' : 'tw-text-slate-400'"
            x-text="selected ? selected.label : @js($placeholder)"></span>
        <i class="fas fa-chevron-down tw-text-xs tw-text-slate-400 tw-transition" x-bind:class="open ? 'tw-rotate-180' : ''" aria-hidden="true"></i>
    </button>

    @if ($clearable)
        <button type="button" x-show="selected && ! @js((bool) $disabled)" x-cloak x-on:click.stop="clear()"
            class="tw-absolute tw-right-8 tw-top-1/2 tw-flex tw-h-7 tw-w-7 tw--translate-y-1/2 tw-items-center tw-justify-center tw-rounded-md tw-border-0 tw-bg-transparent tw-p-0 tw-text-slate-400 hover:tw-text-rose-600 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/40"
            aria-label="{{ $clearLabel ?: __('commontable.clear_selection') }}" title="{{ $clearLabel ?: __('commontable.clear_selection') }}">
            <i class="fas fa-times tw-text-xs" aria-hidden="true"></i>
        </button>
    @endif

    <div x-show="open" x-cloak x-ref="pop" x-bind:style="popStyle" x-transition.opacity.duration.100ms
        class="tw-z-[1070] tw-overflow-hidden tw-rounded-lg tw-border tw-border-solid tw-border-slate-200 tw-bg-white tw-shadow-dialog">
        <div class="tw-border-0 tw-border-b tw-border-solid tw-border-slate-100 tw-p-2">
            <div class="tw-relative">
                <i class="fas fa-search tw-pointer-events-none tw-absolute tw-left-3 tw-top-1/2 tw--translate-y-1/2 tw-text-xs tw-text-slate-400" aria-hidden="true"></i>
                <input type="search" x-ref="search" x-model="query" autocomplete="off"
                    x-on:input="active = 0"
                    x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)"
                    x-on:keydown.enter.prevent.stop="enter()" x-on:keydown.tab="close(false)"
                    placeholder="{{ $searchPlaceholder ?: __('commontable.type_to_search') }}"
                    aria-label="{{ $searchPlaceholder ?: __('commontable.type_to_search') }}"
                    class="tw-block tw-min-h-10 tw-w-full tw-rounded-md tw-border tw-border-solid tw-border-slate-300 tw-bg-white tw-py-1.5 tw-pl-8 tw-pr-3 tw-text-sm tw-text-slate-900 placeholder:tw-text-slate-400 focus:tw-border-brand-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-brand-500/20">
            </div>
        </div>

        {{-- Up to 10 rows are visible (fewer when the screen is short); the rest scroll --}}
        <ul id="{{ $id }}-list" x-ref="list" role="listbox" tabindex="-1" x-bind:style="'max-height:' + listMax + 'px'"
            class="tw-m-0 tw-list-none tw-overflow-y-auto tw-overscroll-contain tw-p-1">
            <template x-for="(option, index) in filtered" x-bind:key="option.value">
                <li role="option" x-bind:aria-selected="(option.value === String(value ?? '')).toString()"
                    x-bind:data-active="(index === active).toString()"
                    x-on:click="choose(option)" x-on:mousemove="active = index"
                    x-bind:class="{
                        'tw-bg-brand-600 tw-text-white': index === active,
                        'tw-text-slate-800': index !== active,
                        'tw-font-semibold': option.value === String(value ?? '')
                    }"
                    class="tw-flex tw-min-h-9 tw-cursor-pointer tw-items-center tw-rounded-md tw-px-3 tw-py-1.5 tw-text-sm">
                    <span class="tw-min-w-0 tw-flex-1 tw-break-words" x-text="option.label"></span>
                    <i class="fas fa-check tw-ml-2 tw-text-xs" x-show="option.value === String(value ?? '')" aria-hidden="true"></i>
                </li>
            </template>
            <li x-show="filtered.length === 0" class="tw-px-3 tw-py-3 tw-text-center tw-text-sm tw-text-slate-500">
                {{ $emptyText ?: __('commontable.dt_zero_records') }}
            </li>
        </ul>
    </div>
</div>
