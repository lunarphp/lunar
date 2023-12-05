<form method="POST" wire:submit.prevent="save">
  <div class="space-y-4">
    @include('shipping::partials.forms.shipping-method-top')

    <x-hub::input.group label="Minimum Spend" for="minimum_spend" :error="$errors->first('data.minimum_spend')">
      <div class="flex items-center">
        <div class="grow">
          <x-hub::input.price
            wire:model="data.minimum_spend.{{ $currency->code }}"
            :symbol="$this->currency->format"
            :currencyCode="$this->currency->code"
            placeholder="0.00"
            :error="$errors->first('data.minimum_spend')"
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
    </x-hub::input.group>

    <x-hub::input.group
      label="Use discounted amount"
      for="use_discount_amount"
      :error="$errors->first('data.use_discount_amount')"
      instructions="When checked, take the discounted subtotal to determine whether to apply free shipping"
    >
      <x-hub::input.toggle wire:model="data.use_discount_amount" id="use_discount_amount" />
    </x-hub::input.group>

    @include('shipping::partials.forms.product-exclusions')

    <div class="mt-4">
      <x-hub::button>Save Method</x-hub::button>
    </div>
  </div>
</form>
