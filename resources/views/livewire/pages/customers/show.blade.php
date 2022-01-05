<div class="flex-col space-y-4">
  @livewire('hub.components.customers.show', [
    'customer' => $customer->load([
      'users',
      'customerGroups',
    ]),
  ])
</div>
