<div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
        <header>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                Conditions
            </h3>
        </header>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-hub::input.group
                    for="coupon"
                    label="Coupon"
                    :error="$errors->first('discount.coupon')"
                    instructions="Enter the coupon required for the discount to apply, if left blank it will apply automatically."
                >
                    <x-hub::input.text wire:model.defer="discount.coupon" id="discount" />
                </x-hub::input.group>

                <div>
                    <x-hub::input.group for="max_uses" :error="$errors->first('discount.max_uses')" :label="__('adminhub::inputs.max_uses.label')" instructions="Leave blank for unlimited uses.">
                        <x-hub::input.text type="number" wire:model="discount.max_uses" />
                    </x-hub::input.group>
                </div>
            </div>


            <div class="grid grid-cols-2 gap-4">
                <div>
                    <header class="flex items-center justify-end">
                          <select wire:change="setCurrency($event.target.value)" class="py-1 pl-2 pr-8 text-base text-gray-600 bg-gray-100 border-none rounded-md form-select focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
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
                <div>
                    <x-hub::input.group for="max_uses_per_user" :error="$errors->first('discount.max_uses_per_user')" :label="__('adminhub::inputs.max_uses_per_user.label')" instructions="Leave blank for unlimited uses.">
                        <x-hub::input.text type="number" wire:model="discount.max_uses_per_user" />
                    </x-hub::input.group>
                </div>
            </div>
        </div>
    </div>
</div>
