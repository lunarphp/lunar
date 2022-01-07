<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.inventory.title') }}
      </h3>
    </header>

    <div class="space-y-4">
      <div class="grid grid-cols-3 gap-4">
        <x-hub::input.group :label="__('adminhub::inputs.stock.label')" for="stock" :error="$errors->first('variant.stock')">
          <x-hub::input.text type="number" wire:model="variant.stock" id="stock" :error="$errors->first('variant.stock')" />
        </x-hub::input.group>

        <x-hub::input.group :label="__('adminhub::inputs.backorder.label')" for="backorder" :error="$errors->first('variant.backorder')">
          <x-hub::input.text type="number" wire:model="variant.backorder" id="backorder" :error="$errors->first('variant.backorder')" />
        </x-hub::input.group>

        <div>
          <x-hub::input.group :label="__('adminhub::inputs.purchasable.label')" for="purchasable" :error="$errors->first('variant.purchasable')">
            <x-hub::input.select wire:model="variant.purchasable" id="purchasable" :error="$errors->first('variant.purchasable')">
              <option value="always">{{ __('adminhub::partials.inventory.options.always') }}</option>
              <option value="in_stock">{{ __('adminhub::partials.inventory.options.in_stock') }}</option>
              <option value="backorder">{{ __('adminhub::partials.inventory.options.backorder') }}</option>
            </x-hub::input.select>
          </x-hub::input.group>
        </div>
      </div>

      <x-hub::alert>
        {{ __('adminhub::partials.inventory.purchasable.'.$variant->purchasable) }}
      </x-hub::alert>
    </div>
  </div>
</div>
