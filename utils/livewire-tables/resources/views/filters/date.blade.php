<div>
    <label for="{{ $field }}"
           class="lt-block lt-text-xs lt-font-medium lt-text-gray-700 lt-capitalize">
        {{ $heading }}
    </label>

    <div x-data="{
        value: @entangle('filters.' . $field),
        init() {
            this.$nextTick(() => {
                flatpickr($refs.input, {
                    mode: 'range',
                })
            })
        }
    }"
         x-on:change="value = $event.target.value"
         class="lt-flex lt-relative lt-mt-1">
        <x-hub::input.text x-ref="input"
                           type="text"
                           x-bind:value="value"
                           class="lt-min-w-[-webkit-fill-available]" />
        <div x-show="value"
             class="lt-absolute lt-right-0 lt-mr-3">
            <button x-on:click="value = null"
                    type="button"
                    class="lt-inline-flex lt-items-center lt-text-sm lt-text-gray-400 lt-hover:text-gray-800">
                <x-hub::icon ref="x-circle"
                             class="lt-w-4 l-h-4 lt-mt-2" />
            </button>
        </div>
    </div>
</div>
