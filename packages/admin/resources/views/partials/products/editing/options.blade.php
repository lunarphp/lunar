<div>
  @livewire('hub.components.product-options.option-manager', [
    'options' => $options,
    'selectedValues' => $optionValues,
  ])
  
  @livewire('hub.components.products.options.option-selector', [
    'selected' => $options->pluck('id')->toArray(),
    'openPanel' => $openPanel ?? true,
  ])
</div>
