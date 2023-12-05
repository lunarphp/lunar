<div x-data="opayo({
  $wire,
  processing: @entangle('processing'),
  identifier: @entangle('identifier'),
  merchantKey: @entangle('merchantKey'),
  name: '{{ $this->billing->first_name }} {{ $this->billing->last_name }}'
})">
  @if($showChallenge)
    @include('lunar::opayo.partials.threed-secure-modal')
  @endif

  <form class="space-y-2" x-on:submit.prevent="handleSubmit()">
      <label class="space-y-1">
        <span class="text-sm font-medium">Cardholder Name</span>
        <input type="text" x-model="name" class="w-full border-gray-300 rounded shadow-sm" />
      </label>
      <div class="flex space-x-2">
        <label class="space-y-1 grow">
          <span class="text-sm font-medium">Card Number</span>
          <input type="text" x-model="card" x-ref="card" class="w-full border-gray-300 rounded shadow-sm" placeholder="0000 0000 0000 0000" />
        </label>

        <label class="w-24 space-y-1">
          <span class="text-sm font-medium">CVV</span>
          <input type="number" x-model="cvv" class="w-full border-gray-300 rounded shadow-sm" placeholder="123" />
        </label>

        <label class="w-24 space-y-1">
          <span class="text-sm font-medium">Expiry</span>
          <input type="text" x-model="expiry" x-ref="expiry" class="w-full border-gray-300 rounded shadow-sm" placeholder="MM/YY" />
        </label>
      </div>

        <button
          class="flex items-center px-5 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500 disabled:opacity-50"
          type="submit"
          x-bind:disabled="processing"
        >
          <span
            x-show="!processing"
          >
            Make Payment
          </span>
          <span
            x-show="processing"
            class="block mr-2"
          >
            <svg
              class="w-5 h-5 text-white animate-spin"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
          </span>
          <span
            x-show="processing"
          >
            Processing
          </span>
      </button>
    </form>

    <div x-show="errors.length" class="p-4 mt-4 space-y-2 rounded bg-red-50" x-cloak>
      <template x-for="(error, errorIndex) in errors" :key="errorIndex" hidden>
        <span x-text="error.message" class="block text-red-600"></span>
      </template>
    </div>

    @if($error)
      <div class="p-4 mt-4 space-y-2 rounded bg-red-50">
        <span class="block text-red-600">
          {{ $error }}
        </span>
      </div>
    @endif
</div>