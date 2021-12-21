<div class="flex-col space-y-4">
  @livewire('hub.components.orders.show', [
    'order' => $order->load([
      'transactions',
      'addresses',
      'lines.currency',
    ]),
  ])
</div>
