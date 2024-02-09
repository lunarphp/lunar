<div>
  <x-hub::slideover title="Ship by weight/total" wire:model="showShipByTotal">
    <div class="space-y-4">
      <x-hub::input.group label="Display Name" for="name" :error="$errors->first('currency.name')">
        <x-hub::input.text value="Standard Shipping" name="name" id="name" :error="$errors->first('currency.name')" />
      </x-hub::input.group>

      <x-hub::input.group label="Description" for="name" :error="$errors->first('currency.name')">
        <x-hub::input.textarea value="Standard Shipping" name="name" id="name" :error="$errors->first('currency.name')" />
      </x-hub::input.group>

      <x-hub::input.group label="Calculation Method" for="type" :error="$errors->first('currency.name')">
        <x-hub::input.select for="type">
          <option>Order Subtotal (Incl. discount)</option>
          <option>Order Subtotal</option>
          <option selected>Weight</option>
        </x-hub::input.select>
      </x-hub::input.group>

      <div class="mt-4">
        <strong>Quantity Breaks</strong><br>

        <div class="space-y-4">
          <div class="grid items-center grid-cols-4 gap-2">
            <x-hub::input.group label="Min Price" for="name" :error="$errors->first('currency.name')">
              <x-hub::input.text placeholder="0.00"  :error="$errors->first('currency.name')" />
            </x-hub::input.group>

            <x-hub::input.group label="Calculation Method" for="type" :error="$errors->first('currency.name')">
              <x-hub::input.select for="type">
                <option selected>Any</option>
                @foreach($this->customerGroups as $group)
                  <option>{{ $group->name }}</option>
                @endforeach
              </x-hub::input.select>
            </x-hub::input.group>

            <x-hub::input.group label="Cost" for="name" :error="$errors->first('currency.name')">
              <x-hub::input.text placeholder="0.00"  :error="$errors->first('currency.name')" />
            </x-hub::input.group>

            <x-hub::icon ref="trash" class="w-4" />
          </div>

          <div class="grid items-center grid-cols-4 gap-2">
            <x-hub::input.group label="Min Price" for="name" :error="$errors->first('currency.name')">
              <x-hub::input.text placeholder="0.00"  :error="$errors->first('currency.name')" />
            </x-hub::input.group>

            <x-hub::input.group label="Calculation Method" for="type" :error="$errors->first('currency.name')">
              <x-hub::input.select for="type">
                <option selected>Any</option>
                @foreach($this->customerGroups as $group)
                  <option>{{ $group->name }}</option>
                @endforeach
              </x-hub::input.select>
            </x-hub::input.group>

            <x-hub::input.group label="Cost" for="name" :error="$errors->first('currency.name')">
              <x-hub::input.text placeholder="0.00"  :error="$errors->first('currency.name')" />
            </x-hub::input.group>

          </div>
        </div>

        <button class="w-full py-1 mt-4 text-gray-600 bg-gray-50">Add tier</button>
      </div>

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
    <x-slot name="footer">
      <x-hub::button>Save Changes</x-hub::button>
    </x-slot>
  </x-hub::slideover>
</div>