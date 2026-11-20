{{--
    One form field in a vehicle management modal.
      $name      Livewire property
      $label     label text
      $type      text | email | tel | number | textarea | select | date
      $required  bool
      $options   [value => label] for select
      $max       maxlength (text)
      $lang      "bn" for Bangla input
      $hint      small help text under the field
      $wide      true: spans both columns
      $blur      true: the value is sent to the server when the field is left (text / textarea)
--}}
@php
    $ui = \App\Livewire\Concerns\Ui::class;
    $type = $type ?? 'text';
    $fid = 'vf-' . \Illuminate\Support\Str::kebab($name);
    $hasError = $errors->has($name);
    $model = 'wire:model' . (! empty($blur) ? '.blur' : '');
@endphp
<div class="{{ ! empty($wide) ? 'sm:tw-col-span-2' : '' }}">
    <label for="{{ $fid }}" class="{{ $ui::LABEL }}">{{ $label }} @if (! empty($required))<span class="tw-text-rose-600">*</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="{{ $fid }}" rows="3" {{ $model }}="{{ $name }}" @if (! empty($max)) maxlength="{{ $max }}" @endif
            class="{{ $ui::INPUT }} tw-py-2 @if ($hasError) {{ $ui::INPUT_ERROR }} @endif" @if (! empty($lang)) lang="{{ $lang }}" @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $fid }}-error" @endif></textarea>
    @elseif ($type === 'select')
        <select id="{{ $fid }}" wire:model="{{ $name }}"
            class="{{ $ui::INPUT }} @if ($hasError) {{ $ui::INPUT_ERROR }} @endif"
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $fid }}-error" @endif>
            <option value="">— {{ __('vehicle.choose') }} —</option>
            @foreach ($options ?? [] as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        </select>
    @elseif ($type === 'date')
        <x-ui.date-field :id="$fid" wire:model="{{ $name }}" :invalid="$hasError" />
    @else
        <input id="{{ $fid }}" type="{{ $type }}" {{ $model }}="{{ $name }}" autocomplete="off"
            @if (! empty($max)) maxlength="{{ $max }}" @endif @if (! empty($lang)) lang="{{ $lang }}" @endif
            @if ($type === 'tel') inputmode="tel" placeholder="01XXXXXXXXX" @endif
            @if ($type === 'number') step="0.01" min="0" inputmode="decimal" @endif
            class="{{ $ui::INPUT }} @if ($hasError) {{ $ui::INPUT_ERROR }} @endif"
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $fid }}-error" @endif>
    @endif
    @error($name) <p id="{{ $fid }}-error" class="{{ $ui::ERROR }}"><i class="fas fa-exclamation-circle tw-mt-0.5" aria-hidden="true"></i>{{ $message }}</p> @enderror
    @if (! empty($hint)) <p class="{{ $ui::HINT }}">{{ $hint }}</p> @endif
</div>
