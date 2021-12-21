<!-- This example requires Tailwind CSS v2.0+ -->
<!-- Enabled: "bg-indigo-600", Not Enabled: "bg-gray-200" -->
<div
  x-data="{
    checked: @if($attributes->wire('model')->value()) @entangle($attributes->wire('model')) @else {{ $on ? 'true' : 'false' }} @endif
  }"
>
  <button
    {{ $attributes->except(['disabled', 'wire:model']) }}
    type="button"
    class="{{ $on ? 'bg-green-500' : 'bg-gray-200' }} relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
    role="switch"
    aria-checked="false"
    @if($disabled ?? true) disabled @endif
    x-on:click="checked = !checked"
    :class="{
        'bg-green-500': checked,
    }"
  >
    @if($attributes->wire('model')->value())
      <input type="checkbox" x-model="checked" class="hidden" value="1" />
    @endif
    <span
      aria-hidden="true"
      :class="{
        'translate-x-5': checked,
      }"
      class="{{ $on ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition ease-in-out duration-200"
    ></span>
  </button>
</div>
