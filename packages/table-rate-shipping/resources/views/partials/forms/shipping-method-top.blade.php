<x-hub::input.group label="Display Name" for="name" :error="$errors->first('shippingMethod.name')">
  <x-hub::input.text wire:model="shippingMethod.name" name="name" id="name" :error="$errors->first('shippingMethod.name')" />
</x-hub::input.group>

<x-hub::input.group label="Description" for="description" :error="$errors->first('shippingMethod.description')">
  <x-hub::input.richtext
    wire:model="shippingMethod.description"
    name="description"
    id="description"
    :error="$errors->first('shippingMethod.description')"
    :initial-value="$shippingMethod->description"
  />
</x-hub::input.group>

<x-hub::input.group label="Code" for="code" :error="$errors->first('shippingMethod.code')">
  <x-hub::input.text wire:model="shippingMethod.code" name="code" id="code" :error="$errors->first('shippingMethod.code')" />
</x-hub::input.group>

<x-hub::input.group label="Cut off" for="cutoff" :error="$errors->first('shippingMethod.code')">
  <x-hub::input.text type="time" wire:model="shippingMethod.cutoff" name="cutoff" id="cutoff" :error="$errors->first('shippingMethod.cutoff')" />
</x-hub::input.group>

<x-hub::input.group label="Stock of all basket items must be available" for="stock_available" :error="$errors->first('shippingMethod.stock_available')">
  <x-hub::input.toggle wire:model="shippingMethod.stock_available" name="stock_available" id="stock_available" :error="$errors->first('shippingMethod.stock_available')" />
</x-hub::input.group>
