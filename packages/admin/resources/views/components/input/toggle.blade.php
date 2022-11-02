<div x-data="{
  checked: @if ($attributes->wire('model')->value()) @entangle($attributes->wire('model')) @else {{ $on ? 'true' : 'false' }} @endif,
  onValue: @if (is_bool($onValue)) true @else '{{ $onValue }}' @endif,
  offValue: @if (is_bool($offValue)) false @else '{{ $offValue }}' @endif,
  toggle() { this.checked = this.checked == this.onValue ? this.offValue : this.onValue }
}"
   x-init="if (!checked) { checked = offValue }"
   class="{{ $attributes->get('class') }}">
  <button {{ $attributes->except(['disabled', 'wire:model', 'class']) }}
          type="button"
          @class([
              'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500',
              'bg-green-500' => $on,
              'bg-gray-200' => !$on
          ])
          role="switch"
          aria-checked="false"
          @if ($disabled ?? true) disabled @endif
          x-on:click="toggle"
          :class="{
              'bg-green-500': checked == onValue,
              'bg-gray-200': checked != onValue
          }">
      @if ($attributes->wire('model')->value())
          <input type="checkbox"
                 x-model="checked"
                 class="hidden"
                 value="{{ $onValue }}" />
      @endif

      <span aria-hidden="true"
            :class="{
                'translate-x-5': checked == onValue,
                'translate-x-0': checked != onValue
            }"
            class="{{ $on ? 'translate-x-5' : 'translate-x-0' }} pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition ease-in-out duration-200"></span>
  </button>
</div>
