<div class="flex-col px-8 space-y-4 md:px-12">
  <div class="space-y-4">
    <div class="shadow sm:rounded-md">
      <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
        <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('currency.name')">
          <x-hub::input.text value="United Kingdom" name="name" id="name" :error="$errors->first('currency.name')" />
        </x-hub::input.group>

        <x-hub::input.group label="Type" for="type" :error="$errors->first('currency.name')">
          <x-hub::input.select for="type">
            <option>Limit to Countries</option>
            <option>Limit to States / Provinces</option>
            <option selected>Limit to list of Postcodes</option>
          </x-hub::input.select>
        </x-hub::input.group>

        <x-hub::input.group label="Postcodes" for="type" :error="$errors->first('currency.name')">
          <x-hub::input.textarea for="type">
            CM3 3GA
            NW1 1TX
          </x-hub::input.textarea>
        </x-hub::input.group>
      </div>
    </div>
    <div class="shadow sm:rounded-md">
      <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
        <header>
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            Shipping Methods
          </h3>
        </header>

        <div class="space-y-4">
          <div class="flex items-center justify-between pb-4 border-b">
            <div class="grow">
              <strong>Free Shipping</strong>
              <p class="text-sm text-gray-500">Enable free shipping on your checkout</p>
            </div>

            <div class="ml-4">
              <x-hub::input.toggle :on="true" />
            </div>

            <div class="ml-4">
              <x-hub::button wire:click="$set('showFreeShipping', true)">Edit</x-hub::button>
            </div>
          </div>

          <div class="flex items-center justify-between pb-4 border-b">
            <div class="grow">
              <strong>Flat Rate</strong>
              <p class="text-sm text-gray-500">Charge a fixed shipping cost per order or per item.</p>
            </div>

            <div class="ml-4">
              <x-hub::input.toggle :on="true" />
            </div>

            <div class="ml-4">
              <x-hub::button type="button" wire:click="$set('showFlatRateShipping', true)">Edit</x-hub::button>
            </div>
          </div>

          <div class="flex items-center justify-between pb-4 border-b">
            <div class="grow">
              <strong>Ship by weight/total</strong>
              <p class="text-sm text-gray-500">Calculate shipping cost based on order value or the total weight of items.</p>
            </div>

            <div class="ml-4">
              <x-hub::input.toggle :on="true" />
            </div>

            <div class="ml-4">
              <x-hub::button type="button" wire:click="$set('showShipByTotal', true)">Edit</x-hub::button>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div class="grow">
              <strong>Collection in store</strong>
              <p class="text-sm text-gray-500">Allow customers to pick up their order in store.</p>
            </div>

            <div class="ml-4">
              <x-hub::input.toggle :on="false" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @include('shipping::partials.ship-by-total')
  @include('shipping::partials.free-shipping')
  @include('shipping::partials.flat-rate')
</div>