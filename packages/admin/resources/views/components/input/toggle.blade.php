<div x-data="{
    checked: @if ($attributes->wire('model')->value()) @entangle($attributes->wire('model')) @else {{ $on ? 'true' : 'false' }} @endif,
    onValue: @if (is_bool($onValue)) true @else '{{ $onValue }}' @endif,
    offValue: @if (is_bool($offValue)) false @else '{{ $offValue }}' @endif,
    toggle() {
        this.checked = this.checked == this.onValue ? this.offValue : this.onValue
    }
}"
     x-init="if (!checked) {
         checked = offValue
     }">
    <button {{ $attributes->except(['disabled', 'wire:model']) }}
            type="button"
            class="relative inline-flex flex-shrink-0 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer w-11 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            role="switch"
            aria-checked="false"
            @if ($disabled ?? true) disabled @endif
            x-on:click="toggle"
            :class="{
                'bg-green-500': checked == onValue,
                'bg-gray-200': checked == offValue,
            }">
        @if ($attributes->wire('model')->value())
            <input type="checkbox"
                   x-model="checked"
                   class="hidden"
                   value="{{ $onValue }}" />
        @endif

        <span aria-hidden="true"
              class="inline-block w-5 h-5 transition duration-200 ease-in-out bg-white rounded-full shadow pointer-events-none ring-0"
              :class="{
                  'translate-x-5': checked == onValue,
                  'translate-x-0': checked == offValue,
              }"></span>
    </button>
</div>
