<div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
        <header>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                {{-- TODO: check discount types--}}
                Discount Type
            </h3>
        </header>

        <div class="grid grid-cols-2">
            <x-hub::input.group for="type" label="Type">
                <x-hub::input.select wire:model="discount.type">
                @foreach($this->discountTypes as $discountType)
                    <option value="{{ get_class($discountType) }}">
                        {{ $discountType->getName() }}
                    </option>
                @endforeach
                </x-hub::input.select>
            </x-hub::input.group>
        </div>

        @if($discountComponent = $this->getDiscountComponent())
            @livewire($discountComponent->getName(), [
                'errors' => $errors,
                'discount' => $discount,
            ], key('ui_'.$discount->type))
        @endif
    </div>
</div>
