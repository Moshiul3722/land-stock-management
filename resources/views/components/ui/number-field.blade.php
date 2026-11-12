{{--
    Whole-number field that shows its digits in the page language (inside `.tw-root`).
    In Bangla the value is displayed as ০-৯ whichever digits are typed (0-9 or ০-৯); the
    Livewire property always receives plain 0-9.

    With `stepper`, − and + buttons sit either side, and Up/Down arrows change the value,
    like a native number input.

    <x-ui.number-field id="qty" wire:model="pickQuantity" stepper :min="1" :invalid="$errors->has('pickQuantity')" />
--}}
@props(['id', 'invalid' => false, 'maxlength' => 7, 'stepper' => false, 'min' => 0, 'max' => 9999999])

@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $bangla = app()->getLocale() === 'bn';
    $stepButton = 'tw-flex tw-h-11 tw-w-10 tw-flex-none tw-items-center tw-justify-center tw-border tw-border-solid tw-border-slate-300 tw-bg-slate-50 tw-text-slate-600 tw-transition hover:tw-bg-slate-100 hover:tw-text-slate-900 focus:tw-outline-none focus-visible:tw-relative focus-visible:tw-z-10 focus-visible:tw-ring-2 focus-visible:tw-ring-brand-500/50 disabled:tw-cursor-not-allowed disabled:tw-opacity-40';
@endphp

<div class="tw-flex tw-items-stretch"
    x-data="{
        value: @entangle($attributes->wire('model')),
        bangla: @js($bangla),
        min: @js((int) $min),
        max: @js((int) $max),
        toLatin(text) { return String(text ?? '').replace(/[০-৯]/g, d => '০১২৩৪৫৬৭৮৯'.indexOf(d)).replace(/[^0-9]/g, ''); },
        show(text) {
            const latin = this.toLatin(text);
            return this.bangla ? latin.replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d]) : latin;
        },
        get number() { const n = parseInt(this.toLatin(this.value), 10); return Number.isNaN(n) ? null : n; },
        step(delta) {
            const next = Math.min(this.max, Math.max(this.min, (this.number ?? (delta > 0 ? this.min - 1 : this.min)) + delta));
            this.value = String(next);
        }
    }">
    @if ($stepper)
        <button type="button" x-on:click="step(-1)" x-bind:disabled="number !== null && number <= min" tabindex="-1"
            aria-label="{{ __('commontable.decrease') }}" title="{{ __('commontable.decrease') }}"
            class="{{ $stepButton }} tw-rounded-l-lg tw-border-r-0">
            <i class="fas fa-minus tw-text-xs" aria-hidden="true"></i>
        </button>
    @endif

    <input type="text" id="{{ $id }}" inputmode="numeric" autocomplete="off" maxlength="{{ $maxlength }}"
        x-bind:value="show(value)"
        x-on:input="value = toLatin($event.target.value); $event.target.value = show(value)"
        @if ($stepper)
            role="spinbutton" aria-valuemin="{{ (int) $min }}" x-bind:aria-valuenow="number"
            x-on:keydown.arrow-up.prevent="step(1)" x-on:keydown.arrow-down.prevent="step(-1)"
        @endif
        @if ($invalid) aria-invalid="true" @endif
        {{ $attributes->whereDoesntStartWith('wire:model')->merge([
            'class' => $ui::INPUT . ' tw-min-w-0 tw-flex-1 tw-tabular-nums ' . ($stepper ? 'tw-rounded-none tw-text-center focus:tw-relative focus:tw-z-10 ' : '') . ($invalid ? $ui::INPUT_ERROR : ''),
        ]) }}>

    @if ($stepper)
        <button type="button" x-on:click="step(1)" x-bind:disabled="number !== null && number >= max" tabindex="-1"
            aria-label="{{ __('commontable.increase') }}" title="{{ __('commontable.increase') }}"
            class="{{ $stepButton }} tw-rounded-r-lg tw-border-l-0">
            <i class="fas fa-plus tw-text-xs" aria-hidden="true"></i>
        </button>
    @endif
</div>
