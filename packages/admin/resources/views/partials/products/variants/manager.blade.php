<div class="flex flex-col gap-2">
    @if(sizeof($variants))
        <div class="flex items-center justify-end space-x-2">
            <div>
                <select wire:change="setCurrency($event.target.value)" class="block w-full py-1 pl-2 pr-8 text-base text-gray-600 bg-gray-100 border-none rounded-md form-select focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @foreach($this->currencies as $c)
                        <option value="{{ $c->id }}" @if($currency->id == $c->id) selected @endif>{{ $c->code }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($variants as $key => $variant)
                <div class="space-y-2 px-1.5 py-2 even:bg-gray-50 even:rounded-md even:border">
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            @foreach($variant['labels'] as $label)
                                <span class="text-sm text-gray-800">{{ $label['option'] }}: {{ $label['value'] }}</span>
                            @endforeach
                        </div>

                        @if(!empty($variant['id']))
                            <a href="{{  route('hub.products.variants.show', [
                                'product' => $product->id,
                                'variant' => $variant['id'],
                            ]) }}">
                                <x-hub::icon ref="pencil"
                                        style="solid"
                                        class="text-gray-400 hover:text-indigo-500" />
                            </a>
                        @endif
                    </div>
    
                    <div class="flex gap-2">
    
                        <div class="grid gap-2">
                            @foreach(['sku', 'gtin', 'mpn', 'ean'] as $identifier)
                                @if(config("lunar-hub.products.{$identifier}.required", false))
                                    <x-hub::input.group :label="__('adminhub::inputs.'.$identifier.'.label')" :for="$identifier.'-'.$key" :error="$errors->first('variants.'.$key.'.'.$identifier)" required>
                                        <x-hub::input.text wire:model="variants.{{ $key }}.{{ $identifier }}" :error="$errors->first('variant.'.$key.'.'.$identifier)" :id="$identifier.'-'.$key"/>
                                    </x-hub::input.group>
                                @endif
                            @endforeach
                        </div>
    
                        <div class="grid gap-2">
                            <x-hub::input.group
                                :label="__('adminhub::inputs.base_price_excl_tax.label')"
                                :instructions="__('adminhub::inputs.base_price_excl_tax.instructions')"
                                :for="'basePrices'.$key"
                                :errors="$errors->get('variants.'.$key.'.basePrices.*.price')"
                                required
                            >
                                <x-hub::input.price
                                wire:model="variants.{{ $key }}.basePrices.{{ $this->currency->code }}.price"
                                :error="$errors->first('variants.'.$key.'.basePrices.'.$this->currency->code.'.price')"
                                :currencyCode="$this->currency->code"
                                :id="'basePrices'.$key"
                                required
                                />
                            </x-hub::input.group>

                            <div x-data="{ expand: false }">
                                <x-hub::input.group
                                    :label="__('adminhub::partials.pricing.customer_groups.title')"
                                    for=""
                                >
                                    <x-slot name="labelPrefix">
                                        <button x-cloak
                                                x-on:click.prevent="expand = !expand"
                                                class="mr-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 bg-white rounded border border-gray-200 p-1">
                                            <span :class="{ 'rotate-90': expand }"
                                                class="block transition">
                                                <x-hub::icon ref="chevron-right"
                                                            class="w-3 h-3" />
                                            </span>
                                        </button>
                                    </x-slot>
                                
                                    <div class="mt-2 bg-gray-100/60 border border-gray-200 p-2 rounded-lg" x-show="expand" x-transition>
                                        @foreach($this->customerGroups as $group)
                                            <div wire:key="variant_{{ $key }}_group_price_{{ $group->id }}">
                                                <div class="grid items-center grid-cols-2 gap-4">
                                                    <span class="text-sm text-gray-700">{{ $group->name }}</span>
                                                    <x-hub::input.group
                                                        :label="null"
                                                        for="customerGroupPrices"
                                                        :errors="$errors->get('variants.'.$key.'.customerGroupPrices.'.$group->id.'.*.price')"
                                                        :error-icon="false"
                                                    >
                                                        <x-hub::input.price
                                                            wire:model="variants.{{ $key }}.customerGroupPrices.{{ $group->id }}.{{ $this->currency->code }}.price"
                                                            :currencyCode="$this->currency->code"
                                                            :error="$errors->first('variants.'.$key.'.customerGroupPrices.'.$group->id.'.'.$this->currency->code.'.price')"
                                                            :error-icon="false"
                                                        />
                                                    </x-hub::input.group>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </x-hub::input.group>
                            </div>
                        </div>
                        
                        <x-hub::input.group :label="__('adminhub::inputs.stock.label')" :for="'stock'.$key" :error="$errors->first('variants.'.$key.'.stock')">
                            <x-hub::input.text type="number" wire:model="variants.{{ $key }}.stock" :id="'stock'.$key" :error="$errors->first('variants.'.$key.'.stock')" />
                        </x-hub::input.group>
    
                        <x-hub::input.group :label="__('adminhub::inputs.backorder.label')" :for="'backorder'.$key" :error="$errors->first('variants.'.$key.'.backorder')">
                            <x-hub::input.text type="number" wire:model="variants.{{ $key }}.backorder" :id="'backorder'.$key" :error="$errors->first('variants.'.$key.'.backorder')" />
                        </x-hub::input.group>
                    </div>
                </div>

            @endforeach
        </div>
    @else
        <p class="text-md text-gray-500">{{ __('adminhub::partials.products.variants.empty_message') }}</p>
    @endif
</div>
