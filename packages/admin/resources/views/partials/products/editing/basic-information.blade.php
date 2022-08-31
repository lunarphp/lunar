<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.products.basic-information.heading') }}
      </h3>
    </header>

    <div class="space-y-4">
      <x-hub::input.group :label="__('adminhub::inputs.brand.label')" for="brand">
        <x-hub::input.select id="brand" wire:model="product.brand_id">
          @foreach($this->brands as $brand)
            <option value="{{ $brand->id }}" wire:key="{{ $brand->id }}">{{ $brand->name }}</option>
          @endforeach
        </x-hub::input.select>
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
