<div class="flex-col space-y-4">
    <div class="items-center justify-between md:flex">
      <strong class="block text-lg font-semibold md:text-2xl">
        {{ __('adminhub::orders.index.title') }}
      </strong>
    </div>
    @livewire('hub.components.orders.table')
</div>
