<div class="space-y-4">
    <div class="overflow-hidden shadow sm:rounded-md">
        <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
            <x-hub::input.group for="name" :label="__('adminhub::inputs.name')" :error="$errors->first('discount.name')">
                <x-hub::input.text wire:model.lazy="discount.name" />
            </x-hub::input.group>

            <x-hub::input.group for="name" :label="__('adminhub::inputs.handle')" :error="$errors->first('discount.handle')">
                <x-hub::input.text wire:model.defer="discount.handle" />
            </x-hub::input.group>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-hub::input.group for="starts_at" :label="__('adminhub::inputs.starts_at.label')">
                        <x-hub::input.datepicker wire:model="discount.starts_at" :options="['enableTime' => true ]" />
                    </x-hub::input.group>
                </div>

                <div>
                    <x-hub::input.group for="starts_at" :label="__('adminhub::inputs.ends_at.label')" :error="$errors->first('discount.ends_at')">
                        <x-hub::input.datepicker wire:model="discount.ends_at" :options="['enableTime' => true ]" />
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

            <div>
                <div class="flex items-center justify-between">
                    <div>
                        Limit by collection
                    </div>
                    <div>
                        @livewire('hub.components.collection-search', [
                            'existing' => collect()
                        ])
                    </div>
                </div>

                <div class="space-y-2 mt-4">
                    @foreach ($collections as $index => $collection)
                        <div wire:key="collection_{{ $index }}">
                            <div class="flex items-center px-4 py-2 text-sm border rounded">
                                @if ($collection['thumbnail'])
                                    <span class="flex-shrink-0 block w-12 mr-4">
                                        <img src="{{ $collection['thumbnail'] }}"
                                             class="rounded">
                                    </span>
                                @endif

                                <div class="flex grow">
                                    <div class="grow flex gap-1.5 flex-wrap items-center">
                                        <strong class="rounded px-1.5 py-0.5 bg-blue-50 text-xs text-blue-600">
                                            {{ $collection['group_name'] }}
                                        </strong>

                                        @if (count($collection['breadcrumb']))
                                            <span class="text-gray-500 flex gap-1.5 items-center">
                                                <span class="font-medium">
                                                    {{ collect($collection['breadcrumb'])->first() }}
                                                </span>

                                                <x-hub::icon ref="chevron-right"
                                                             class="w-4 h-4"
                                                             style="solid" />
                                            </span>
                                        @endif

                                        @if (count($collection['breadcrumb']) > 1)
                                            <span class="text-gray-500 flex gap-1.5 items-center"
                                                  title="{{ collect($collection['breadcrumb'])->implode(' > ') }}">
                                                <span class="font-medium cursor-help">
                                                    ...
                                                </span>

                                                <x-hub::icon ref="chevron-right"
                                                             class="w-4 h-4"
                                                             style="solid" />
                                            </span>
                                        @endif

                                        <strong class="text-gray-700 truncate max-w-[40ch]"
                                                title="{{ $collection['name'] }}">
                                            {{ $collection['name'] }}
                                        </strong>
                                    </div>

                                    <div class="flex items-center">
                                        <x-hub::dropdown minimal>
                                            <x-slot name="options">
                                                <x-hub::dropdown.link class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50"
                                                                      :href="route('hub.collections.show', [
                                                                          'group' => $collection['group_id'],
                                                                          'collection' => $collection['id'],
                                                                      ])">
                                                    {{ __('adminhub::partials.products.collections.view_collection') }}
                                                </x-hub::dropdown.link>

                                                <x-hub::dropdown.button wire:click.prevent="removeCollection({{ $index }})"
                                                                        class="flex items-center justify-between px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                    {{ __('adminhub::global.remove') }}
                                                </x-hub::dropdown.button>
                                            </x-slot>
                                        </x-hub::dropdown>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
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

