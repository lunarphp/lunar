<div class="flex-col space-y-4">
    <div class="flex-col px-4 py-5 space-y-4 bg-white shadow sm:rounded-md sm:p-6">
        <x-hub::input.group :label="__('adminhub::inputs.name')"
                            for="name"
                            :error="$errors->first('productType.name')"
                            required>
            <x-hub::input.text wire:model="productType.name"
                               name="name"
                               id="name"
                               :error="$errors->first('productType.name')" />
        </x-hub::input.group>
    </div>

    <div x-data="{ view: 'products' }">
        @if (!$this->variantsDisabled)
            <nav class="flex space-x-4"
                 aria-label="{{ __('adminhub::global.tabs') }}">
                <button type="button"
                        wire:click="$set('view', 'products')"
                        @class([
                            'px-3 py-3 text-sm font-medium rounded-t',
                            'text-gray-800 bg-white' => $view == 'products',
                            'test-gray-500 hover:text-gray-700' => $view != 'products',
                        ])>
                    {{ __('adminhub::partials.product-type.product_attributes_btn') }}
                </button>

                <button type="button"
                        wire:click="$set('view', 'variants')"
                        @class([
                            'px-3 py-3 text-sm font-medium rounded-t',
                            'text-gray-800 bg-white' => $view == 'variants',
                            'test-gray-500 hover:text-gray-700' => $view != 'variants',
                        ])>
                    {{ __('adminhub::partials.product-type.variant_attributes_btn') }}
                </button>
            </nav>
        @endif

        <div class="p-6 bg-white rounded-b shadow">
            @if ($view == 'products')
                @include('adminhub::partials.product-types.attributes', [
                    'type' => 'products',
                ])
            @endif

            @if ($view == 'variants')
                @include('adminhub::partials.product-types.attributes', [
                    'type' => 'variants',
                ])
            @endif
        </div>
    </div>
</div>
