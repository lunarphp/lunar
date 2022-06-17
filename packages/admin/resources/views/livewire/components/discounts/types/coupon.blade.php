<div class="space-y-4">
    <x-hub::input.group for="coupon" label="Coupon" instructions="Coupon codes are case insensitive">
        <x-hub::input.text wire:model="discount.data.coupon" />
    </x-hub::input.group>

    <x-hub::input.group for="type" label="Percentage" >
        <x-hub::input.text wire:model="discount.data.value" />
    </x-hub::input.group>
</div>
