<div class="space-y-4">
    <div class="overflow-hidden shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <x-hub::input.group for="name" :label="__('adminhub::inputs.name')" :error="$errors->first('discount.name')">
                <x-hub::input.text wire:model.lazy="discount.name" id="name" />
            </x-hub::input.group>

            <x-hub::input.group for="handle" :label="__('adminhub::inputs.handle')" :error="$errors->first('discount.handle')" required>
                <x-hub::input.text wire:model.defer="discount.handle" id="handle" />
            </x-hub::input.group>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-hub::input.group for="starts_at" :label="__('adminhub::inputs.starts_at.label')">
                        <x-hub::input.datepicker id="starts_at" wire:model="discount.starts_at" :options="['enableTime' => true ]" />
                    </x-hub::input.group>
                </div>

                <div>
                    <x-hub::input.group for="ends_at" :label="__('adminhub::inputs.ends_at.label')" :error="$errors->first('discount.ends_at')">
                        <x-hub::input.datepicker id="ends_at" wire:model="discount.ends_at" :options="['enableTime' => true ]" />
                    </x-hub::input.group>
                </div>

                <div>
                    <x-hub::input.group for="max_uses" :label="__('adminhub::inputs.max_uses.label')" :error="$errors->first('discount.ends_at')">
                        <x-hub::input.text type="number" wire:model="discount.max_uses" />
                    </x-hub::input.group>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-hub::input.group
                        for="priority"
                        label="Priority"
                        :error="$errors->first('discount.priority')"
                        required
                    >
                        <x-hub::input.text type="number" wire:model.defer="discount.priority" id="priority" />
                    </x-hub::input.group>
                </div>

                <div>
                    <x-hub::input.group
                        for="stop"
                        label="Prevent other discounts applying after this one"
                        :error="$errors->first('discount.stop')"
                    >
                        <x-hub::input.toggle id="stop" wire:model="discount.stop" />
                    </x-hub::input.group>

                </div>
            </div>

            <div>
                <header class="flex items-center justify-end">
                      <select wire:change="setCurrency($event.target.value)" class="py-1 pl-2 pr-8 text-base text-gray-600 bg-gray-100 border-none rounded-md form-select focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @foreach($this->currencies as $c)
                          <option value="{{ $c->id }}" @if($currency->id == $c->id) selected @endif>{{ $c->code }}</option>
                        @endforeach
                      </select>
                </header>

                <x-hub::input.group
                  label="Minimum cart amount"
                  instructions="The minimum cart sub total required for this discount to apply"
                  for="basePrice"
                  :errors="$errors->get('minPrices.*.price')"
                >
                  <x-hub::input.price
                    wire:model="discount.data.min_prices.{{ $this->currency->code }}"
                    :symbol="$this->currency->format"
                    :currencyCode="$this->currency->code"
                  />
                </x-hub::input.group>
            </div>

            <x-hub::input.group
                label="Limit by collection"
                for="brands"
            >

            <div class="rounded border h-full overflow-y-scroll max-h-96 bg-gray-50 px-2">
                @foreach($this->collectionTree as $collectionNode)
                    @include('adminhub::partials.forms.discount.collection-tree-node', [
                        'node' => $collectionNode,
                    ])
                @endforeach
            </div>

            </x-hub::input.group>

            <x-hub::input.group
                label="Limit by brand"
                for="brands"
            >
              <div class="rounded border h-full overflow-y-scroll max-h-96 p-2 bg-gray-50 space-y-2">
                @foreach($this->brands as $brand)
                    <label class="flex items-center space-x-2  bg-white py-2 rounded shadow text-sm px-3 cursor-pointer hover:bg-gray-50" wire:key="av_brand_{{ $brand->id }}">
                      <input type="checkbox" wire:model="selectedBrands" value="{{ $brand->id }}">
                      <div>{{ $brand->name }}</div>
                    </label>
                @endforeach
              </div>
            </x-hub::input.group>

            <x-hub::input.group for="type" label="Type">
                <x-hub::input.select wire:model="discount.type">
                @foreach($this->discountTypes as $discountType)
                    <option value="{{ get_class($discountType) }}">
                        {{ $discountType->getName() }}
                    </option>
                @endforeach
                </x-hub::input.select>
            </x-hub::input.group>

            @if($discountComponent = $this->getDiscountComponent())
                @livewire($discountComponent->getName(), [
                    'errors' => $errors,
                    'discount' => $discount,
                ], key('ui_'.$discount->type))
            @endif
        </div>


        <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
          <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __(
              $discount->id ? 'adminhub::components.discounts.save_btn' : 'adminhub::components.discounts.create_btn'
            ) }}
          </button>
        </div>
    </div>

</div>

