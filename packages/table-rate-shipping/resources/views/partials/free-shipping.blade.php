<div>
  <div class="space-y-4">
    <x-hub::input.group label="Display Name" for="name" :error="$errors->first('currency.name')">
      <x-hub::input.text value="Free Shipping" name="name" id="name" :error="$errors->first('currency.name')" />
    </x-hub::input.group>

    <x-hub::input.group label="Description" for="name" :error="$errors->first('currency.name')">
      <x-hub::input.textarea value="Standard Shipping" name="name" id="name" :error="$errors->first('currency.name')" />
    </x-hub::input.group>

    <x-hub::input.group label="Minimum Spend" for="name" :error="$errors->first('currency.name')">
      <x-hub::input.text placeholder="0.00" name="name" id="name" :error="$errors->first('currency.name')" />
    </x-hub::input.group>

    <x-hub::input.group label="Use discounted amount" for="name" :error="$errors->first('currency.name')">
      <x-hub::input.toggle />
    </x-hub::input.group>

    <header class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            Excluded Products
          </h3>
          <p class="text-sm text-gray-500">Products listed here will be excluded from this shipping method.</p>
        </div>

        <div>
          <x-hub::button>Add product</x-hub::button>
        </div>
      </header>

      <div class="space-y-2">
        @foreach($products as $product)
          <div class="flex items-center justify-between p-2 border rounded">
            <div class="flex items-center">
              <img src="{{ $product->thumbnail->getUrl('small') }}" class="w-6 mr-3 rounded">
              {{ $product->translateAttribute('name') }}
            </div>
            <div>
              <x-hub::icon ref="trash" class="w-4 text-gray-500 hover:text-red-500" />
            </div>
          </div>
        @endforeach
      </div>

  </div>
</div>