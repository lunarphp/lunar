<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.identifiers.title') }}
      </h3>
    </header>

    <div class="space-y-4">
      <x-hub::input.group :label="__('adminhub::inputs.sku.label')" for="sku" :error="$errors->first('variant.sku')">
        <x-hub::input.text wire:model="variant.sku" :error="$errors->first('variant.sku')" />
      </x-hub::input.group>

      <div class="grid grid-cols-2 gap-4">
        <x-hub::input.group :label="__('adminhub::inputs.gtin.label')" :error="$errors->first('variant.gtin')" for="gtin">
          <x-hub::input.text id="gtin" wire:model="variant.gtin" :error="$errors->first('variant.gtin')" />
        </x-hub::input.group>

        <x-hub::input.group :label="__('adminhub::inputs.mpn.label')" :error="$errors->first('variant.mpn')" for="mpn">
          <x-hub::input.text id="mpn" wire:model="variant.mpn" :error="$errors->first('variant.mpn')" />
        </x-hub::input.group>
      </div>

      <x-hub::input.group :label="__('adminhub::inputs.ean.label')" for="ean" :error="$errors->first('variant.ean')">
         <x-hub::input.text id="ean" wire:model="variant.ean" :error="$errors->first('variant.ean')"/>
      </x-hub::input.group>
    </div>
  </div>
</div>
