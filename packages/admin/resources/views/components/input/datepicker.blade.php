<div
  x-data="{
    value: @entangle($attributes->wire('model'))
  }"
  x-init="flatpickr($refs.input, {
    enableTime: {{ $enableTime ? 'true' : 'false' }}
  })"
  @change="value = $event.target.value"
  class="flex"
>
  <x-hub::input.text
    x-ref="input"
    type="text"
    x-bind:value="value"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
  />
  <div x-show="value" class="absolute right-0 mr-3">
    <button x-on:click="value = null" type="button" class="inline-flex items-center text-sm text-gray-400 hover:text-gray-800">
      <x-hub::icon ref="x-circle" class="w-4 mt-2" />
    </button>
  </div>
</div>
