<div class="space-y-4">
    <div>
        <button
            wire:click="$set('discount.data.fixed_value', false)"
            type="button"
            @class([
                'text-sm rounded-md py-1 px-3' => true,
                'bg-gray-100 text-gray-700 font-medium hover:bg-gray-200' => !empty($this->discount->data['fixed_value']),
                'bg-blue-500 text-white font-medium' => empty($this->discount->data['fixed_value'])
            ])
        >
            Percentage
        </button>
        <button
            wire:click="$set('discount.data.fixed_value', true)"
            type="button"
            @class([
                'text-sm rounded-md py-1 px-3' => true,
                'bg-gray-100 text-gray-700 font-medium hover:bg-gray-200' => empty($this->discount->data['fixed_value']),
                'bg-blue-500 text-white font-medium' => $this->discount->data['fixed_value'] ?? false
            ])
        >
            Fixed amount
        </button>
    </div>

    <div @class(['hidden' => !($this->discount->data['fixed_value'] ?? false)])>
        @foreach($this->currencies as $currency)
            <x-hub::input.group
                for="{{ $currency->code }}"
                :label="$currency->name"
                wire:key="currency_{{ $currency->id }}"
                :error="$errors->first('discount.data.fixed_values.'.$currency->code)"
            >
                <x-hub::input.price symbol="" :currencyCode="$currency->code" wire:model.lazy="discount.data.fixed_values.{{ $currency->code }}" />
            </x-hub::input.group>
        @endforeach
    </div>

    <div @class(['hidden' => $this->discount->data['fixed_value'] ?? false])>
        <x-hub::input.group for="type" label="Percentage" :error="$errors->first('discount.data.percentage')" >
            <x-hub::input.text wire:model="discount.data.percentage" type="number" step="any" max="100" />
        </x-hub::input.group>
    </div>


</div>
