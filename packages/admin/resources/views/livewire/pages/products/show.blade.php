<div class="flex-col space-y-4">
  @livewire('hub.components.products.show', [
    'product' => $product->load([
      'variants.prices.currency',
      'variants.prices.priceable'
    ]),
  ])
</div>
