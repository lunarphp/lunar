<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.products.basic-information.heading') }}
      </h3>
    </header>

    <div class="space-y-4">
      <x-hub::input.group :label="__('adminhub::inputs.brand.label')" for="brand" :error="$errors->first('product.brand')">
        <x-hub::input.autocomplete wire:model="product.brand" wire:keydown="getBrands('{{ $product->brand }}')">
          @foreach($brands as $key => $brand)
            <x-hub::input.autocomplete.item wire:key="{{ $key }}" wire:click="$set('product.brand', '{{ $brand }}')">
              {{ $brand }}
            </x-hub::input.autocomplete.item>
          @endforeach
        </x-hub::input.autocomplete>
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.product-type.label')" for="productType">
        <x-hub::input.select id="productType" wire:model="product.product_type_id">
          @foreach($this->productTypes as $type)
            <option value="{{ $type->id }}" wire:key="{{ $type->id }}">{{ $type->name }}</option>
          @endforeach
        </x-hub::input.select>
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.tags.label')" for="tags">
        <x-hub::input.tags id="tags" wire:model="tags" />
      </x-hub::input.group>
    </div>
  </div>
</div>
