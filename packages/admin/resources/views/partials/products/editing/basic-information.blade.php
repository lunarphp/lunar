<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-md sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.products.basic-information.heading') }}
      </h3>
    </header>

    <div class="space-y-4">
      <x-hub::input.group
        :label="__('adminhub::inputs.brand.label')"
        for="brand"
        :errors="$errors->get('product.brand_id') ?: $errors->get('brand')"
    >
        <div class="flex items-center space-x-4">
            <div class="grow">
                @if($useCustomBrand)
                    <x-hub::input.text wire:model="brand" />
                @else
                    <x-hub::input.select id="brand" wire:model="product.brand_id" :error="$errors->first('product.brand_id')">
                      <option value>{{ __('adminhub::components.brands.choose_brand_default_option') }}</option>
                      @foreach($this->brands as $brand)
                        <option value="{{ $brand->id }}" wire:key="{{ $brand->id }}">{{ $brand->name }}</option>
                      @endforeach
                    </x-hub::input.select>
                @endif
            </div>
            <div>
                @if($useCustomBrand)
                    <x-hub::button theme="gray" type="button" wire:click="$set('useCustomBrand', false)">
                        {{ __('adminhub::global.cancel') }}
                    </x-hub::button>
                @else
                    <x-hub::button theme="gray" type="button" wire:click="$set('useCustomBrand', true)">
                        {{ __('adminhub::global.custom') }}
                    </x-hub::button>
                @endif

            </div>
        </div>
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
