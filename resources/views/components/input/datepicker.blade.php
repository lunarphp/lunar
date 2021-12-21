<div
  x-data="{
    value: @entangle($attributes->wire('model'))
  }"
  x-init="flatpickr($refs.input, {
    enableTime: {{ $enableTime ? 'true' : 'false' }}
  })"
  @change="value = $event.target.value"
>
  <x-hub::input.text
    x-ref="input"
    type="text"
    x-bind:value="value"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
  />
</div>
