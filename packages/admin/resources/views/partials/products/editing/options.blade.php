<div>
  @livewire('hub.components.product-options.option-manager', [
    'options' => $options,
  ])
  <div>
    <div class="pt-4 mt-4 space-x-4 border-t">
      @livewire('hub.components.products.options.option-selector', [
        'selected' => $options->pluck('id')->toArray(),
        'openPanel' => $openPanel ?? true,
      ])
    </div>
  </div>
</div>
