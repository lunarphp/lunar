<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        Inventory
      </h3>
    </header>

    <div class="space-y-4">
      <x-hub::input.group label="SKU" for="sku" :error="$errors->first('variant.sku')">
        <x-hub::input.text wire:model="variant.sku" />
      </x-hub::input.group>

      <div class="grid grid-cols-3 gap-4">
        <x-hub::input.group label="Purchasability" instructions="Set the condition this is available to purchase." for="sku">
          <div>
            <x-hub::input.select>
              <option>In Stock</option>
              <option selected>Always</option>
              <option>Expected</option>
            </x-hub::input.select>
          </div>

        </x-hub::input.group>

        <x-hub::input.group label="Min Purchase Quantity" instructions="The minimum amount that can be purchased" for="sku">
          <x-hub::input.text type="number" value="1" />
        </x-hub::input.group>

        <x-hub::input.group label="Min Batch Quantity" instructions="This product can only be ordered in multiples of 1" for="sku">
          <x-hub::input.text type="number" value="1" />
        </x-hub::input.group>
      </div>

      <x-hub::input.group label="Stock Level" for="sku">
        <div class="grid grid-cols-3 text-sm">
          <span class="block">500 Units in stock</span>
          <span class="block">45 Units in transfer</span>
          <a href="#" class="text-indigo-500 hover:underline">Edit stock</a>
        </div>

      </x-hub::input.group>
    </div>
  </div>
</div>
