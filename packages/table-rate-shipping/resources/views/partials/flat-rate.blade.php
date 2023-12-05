<div>
  <x-hub::slideover title="Flate Rate Shipping" wire:model="showFlatRateShipping">
    <div class="space-y-4">
      <x-hub::input.group label="Display Name" for="name" :error="$errors->first('currency.name')">
        <x-hub::input.text value="Flat rate Shipping" name="name" id="name" :error="$errors->first('currency.name')" />
      </x-hub::input.group>

      <x-hub::input.group label="Description" for="name" :error="$errors->first('currency.name')">
        <x-hub::input.textarea value="Standard Shipping" name="name" id="name" :error="$errors->first('currency.name')" />
      </x-hub::input.group>

      <x-hub::input.group label="Type" for="type" :error="$errors->first('currency.name')">
        <x-hub::input.select for="type">
          <option>Per Order</option>
          <option>Per Item</option>
        </x-hub::input.select>
      </x-hub::input.group>

      <x-hub::input.group label="Cost" for="name" :error="$errors->first('currency.name')">
        <x-hub::input.text placeholder="0.00" name="name" id="name" :error="$errors->first('currency.name')" />
      </x-hub::input.group>

    </div>
    <x-slot name="footer">
      <x-hub::button>Save Changes</x-hub::button>
    </x-slot>
  </x-hub::slideover>
</div>