<div class="space-y-4">
    <x-hub::input.group for="coupon" label="Purchase amount" instructions="When there are X items in the cart, the discount applies">
        <x-hub::input.text type="number" wire:model="discount.data.coupon" />
    </x-hub::input.group>

    <h3>Products</h3>
    <p>Select the products required for the discount to apply</p>

    @livewire('hub.components.product-search', [
        'existing' => collect(),
        'ref' => 'product-associations',
        'showBtn' => true,
        {{-- 'exclude' => [$product->id] --}}
     ])
</div>
