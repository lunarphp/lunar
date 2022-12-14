<div>
  <div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
      <header class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            {{ __('adminhub::partials.pricing.title') }}
          </h3>
        </div>
        <div class="flex items-center space-x-2">
          <div>
            <select wire:change="setCurrency($event.target.value)" class="block w-full py-1 pl-2 pr-8 text-base text-gray-600 bg-gray-100 border-none rounded-md form-select focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              @foreach($this->currencies as $c)
                <option value="{{ $c->id }}" @if($currency->id == $c->id) selected @endif>{{ $c->code }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </header>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <x-hub::input.group :label="__('adminhub::inputs.tax_class.label')" for="tax_class">
            <x-hub::input.select id="tax_class" wire:model="variant.tax_class_id">
              @foreach($this->taxClasses as $taxClass)
                <option wire:key="tax_class_{{ $taxClass->id }}" value="{{ $taxClass->id }}">{{ $taxClass->name }}</option>
              @endforeach
            </x-hub::input.select>
          </x-hub::input.group>
        </div>
        <div>
          <x-hub::input.group
            :label="__('adminhub::inputs.tax_ref.label')"
            :instructions="__('adminhub::inputs.tax_ref.instructions')"
            :errors="$errors->get('variant.tax_ref')"
            for="unit_quantity"
          >
            <x-hub::input.text wire:model="variant.tax_ref" id="tax_ref" />
          </x-hub::input.group>
        </div>
      </div>
      <div class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
          <x-hub::input.group
            :label="__('adminhub::inputs.unit_quantity.label')"
            :instructions="__('adminhub::inputs.unit_quantity.instructions')"
            :errors="$errors->get('variant.unit_quantity')"
            for="unit_quantity"
          >
            <x-hub::input.text type="number" wire:model="variant.unit_quantity" id="unit_quantity" />
          </x-hub::input.group>

          <x-hub::input.group
            :label="__('adminhub::inputs.base_price_excl_tax.label')"
            :instructions="__('adminhub::inputs.base_price_excl_tax.instructions')"
            for="basePrice"
            :errors="$errors->get('basePrices.*.price')"
            required
          >
            <x-hub::input.price
              wire:model="basePrices.{{ $this->currency->code }}.price"
              :error="$errors->first('basePrices.'.$this->currency->code.'.price')"
              :currencyCode="$this->currency->code"
              required
            />
          </x-hub::input.group>

          <x-hub::input.group
            :label="__('adminhub::inputs.compare_at_price_excl_tax.label')"
            :instructions="__('adminhub::inputs.compare_at_price_excl_tax.instructions')"
            for="compare_at_price"
            :errors="$errors->get('basePrices.*.compare_price')"
          >
            <x-hub::input.price
              wire:model="basePrices.{{ $this->currency->code }}.compare_price"
              :currencyCode="$this->currency->code"
              :error="$errors->first('basePrices.*.compare_price')"
            />
          </x-hub::input.group>
        </div>
      </div>

      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <strong>{{ __('adminhub::partials.pricing.customer_groups.title') }}</strong>
            <p class="text-xs text-gray-600">
              {{ __('adminhub::partials.pricing.customer_groups.strapline') }}
            </strong>
          </div>

          <x-hub::input.toggle wire:model="customerPricingEnabled" />
        </div>
      </div>

      @if($this->customerPricingEnabled)
        @foreach($this->customerGroups as $group)
          <div wire:key="group_price_{{ $group->id }}">
            <div class="grid items-center grid-cols-2 gap-4">
              {{ $group->name }}
              <x-hub::input.group
                :label="null"
                for="customerGroupPrices"
                :error="$errors->first('customerGroupPrices.'.$group->id.'.'.$currency->code.'.price')"
                :error-icon="false"
              >
                <x-hub::input.price 
                  wire:model="customerGroupPrices.{{ $group->id }}.{{ $currency->code }}.price" 
                  :currencyCode="$currency->code"
                  :error="$errors->first('customerGroupPrices.'.$group->id.'.'.$currency->code.'.price')"
                  :error-icon="false"
                />
              </x-hub::input.group>
            </div>
          </div>
        @endforeach
      @endif

      <div class="flex items-center justify-between pt-4 border-t">
        <div>
          <strong>{{ __('adminhub::partials.pricing.tiers.title') }}</strong>
          <p class="text-xs text-gray-600">
            {{ __('adminhub::partials.pricing.tiers.strapline') }}
          </p>
        </div>
        <x-hub::button :disabled="!$currency->default" wire:click.prevent="addTier" theme="gray" size="sm" type="button">
          {{ __('adminhub::partials.pricing.tiers.add_tier_btn') }}
        </x-hub::button>
      </div>

      <div class="space-y-4">
        @if(count($tieredPrices))
          <div>
          @if(!$this->currency->default)
            <x-hub::alert>
              {{ __('adminhub::partials.pricing.non_default_currency_alert') }}
            </x-hub::alert>
          @endif
          </div>

          @foreach ($tieredPrices as $index => $tier)
            <div class="relative flex divide-y divide-gray-100" wire:key="tier_{{ $index }}">
                <div class="grid w-full grid-cols-6 gap-4 pr-8">
                    <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                        <x-hub::input.group 
                            :label="__('adminhub::global.customer_group')" 
                            for="customer_group_id_field_{{ $index }}"
                            :error="$errors->first('tieredPrices.' . $index . '.customer_group_id')"
                            required
                        >
                            <x-hub::input.select
                                id="customer_group_id_field_{{ $index }}"
                                wire:model='tieredPrices.{{ $index }}.customer_group_id'
                                :disabled="!$this->currency->default">
                                <option value="*">{{ __('adminhub::global.any') }}</option>
                                @foreach ($this->customerGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </x-hub::input.select>
                        </x-hub::input.group>
                    </div>
                    <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                        <x-hub::input.group 
                            :label="__('adminhub::global.lower_limit')"
                            for="tier_field_{{ $index }}"
                            :error="$errors->first('tieredPrices.' . $index . '.tier')"
                            :error-icon="false"
                            required
                        >
                            <x-hub::input.text 
                                id="tier_field_{{ $index }}"
                                wire:model='tieredPrices.{{ $index }}.tier' 
                                type="number"
                                min="2" 
                                steps="1" 
                                required
                                @keydown="$event.keyCode === 190 ? $event.preventDefault() : null"
                                :disabled="!$this->currency->default"
                                :error="$errors->first('tieredPrices.' . $index . '.tier')" />
                        </x-hub::input.group>
                    </div>
                    <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                        <x-hub::input.group 
                            :label="__('adminhub::global.unit_price_excl_tax')"
                            for="price_field_{{ $index }}_lang_{{ $currency->code }}"
                            :error="$errors->first('tieredPrices.' . $index . '.prices.' . $currency->code . '.price')"
                            :error-icon="false"
                            required
                        >
                            <x-hub::input.price
                                id="price_field_{{ $index }}_lang_{{ $currency->code }}"
                                wire:model="tieredPrices.{{ $index }}.prices.{{ $currency->code }}.price"
                                :currencyCode="$this->currency->code" 
                                :error="$errors->first('tieredPrices.' . $index . '.prices.' . $currency->code . '.price')" />
                        </x-hub::input.group>
                    </div>
                </div>
                <button class="absolute right-0 text-gray-500 hover:text-red-500 top-6"
                    wire:click.prevent="removeTier('{{ $index }}')">
                    <x-hub::icon ref="trash" class="w-4" />
                </button>
            </div>
          @endforeach
        @else
        @endif
      </div>
    </div>
  </div>
</div>
