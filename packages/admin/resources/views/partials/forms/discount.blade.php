<div class="space-y-4">
    {{ json_encode($this->discount) }}
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

