<form method="POST" wire:submit.prevent="save">
  <div class="space-y-4">
    @include('shipping::partials.forms.shipping-method-top')

    <header class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('adminhub::partials.pricing.title') }}
        </h3>
      </div>
      <div class="flex items-center space-x-2">
        <div>
          <select wire:change="setCurrency($event.target.value)" class="block w-full py-1 pl-2 pr-8 text-base text-gray-600 bg-gray-100 border-none rounded-md form-select focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            @foreach($this->currencies as $c)
              <option value="{{ $c->id }}" @if($currency->id == $c->id) selected @endif>{{ $c->code }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </header>

    <x-hub::input.group
      label="Shipping cost"
      for="basePrice"
      :errors="$errors->get('basePrices.*.price')"
      required
    >
      <x-hub::input.price
        wire:model="basePrices.{{ $this->currency->code }}.price"
        :symbol="$this->currency->format"
        :currencyCode="$this->currency->code"
        required
      />
    </x-hub::input.group>

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <strong>{{ __('adminhub::partials.pricing.customer_groups.title') }}</strong>
          <p class="text-xs text-gray-600">
            {{ __('adminhub::partials.pricing.customer_groups.strapline') }}
          </strong>
        </div>

        <x-hub::input.toggle wire:model="customerPricingEnabled" />
      </div>
    </div>

    @if($this->customerPricingEnabled)
      @foreach($this->customerGroups as $group)
        <div wire:key="group_price_{{ $group->id }}">
          <div class="grid items-center grid-cols-2 gap-4">
            {{ $group->name }}
            <x-hub::input.group
              :label="null"
              for="customerGroupPrices"
            >
              <x-hub::input.price wire:model="customerGroupPrices.{{ $group->id }}.{{ $currency->code }}.price" :symbol="$currency->format" :currencyCode="$currency->code" />
            </x-hub::input.group>
          </div>
          @foreach($errors->get('customerGroupPrices.'.$group->id.'.*') as $error)
            @foreach($error as $text)
              <p class="mt-2 text-sm text-red-600">{{ $text }}</p>
            @endforeach
          @endforeach
        </div>
      @endforeach
    @endif

    {{-- <x-hub::input.group label="Minimum Spend" for="minimum_spend" :error="$errors->first('data.minimum_spend')">
      <div class="flex items-center">
        <div class="grow">
          <x-hub::input.price
            wire:model="data.price.{{ $currency->code }}"
            :symbol="$this->currency->format"
            :currencyCode="$this->currency->code"
            placeholder="0.00"
            :error="$errors->first('data.price')"
            name="minimum_spend"
            id="minimum_spend"
          />
        </div>

        <div class="ml-4">
          <x-hub::input.select wire:change="setCurrency($event.target.value)">
            @foreach($this->currencies as $c)
              <option value="{{ $c->id }}" @if($currency->id == $c->id) selected @endif>{{ $c->code }}</option>
            @endforeach
          </x-hub::input.select>
        </div>
      </div>
    </x-hub::input.group> --}}

    <x-hub::button>Save Method</x-hub::button>
  </div>
</form>
