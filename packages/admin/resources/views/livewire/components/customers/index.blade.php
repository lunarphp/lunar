<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-xl font-semibold md:text-2xl">
            {{ __('adminhub::catalogue.customers.index.title') }}
        </strong>
    </div>

    <div class="space-y-4">
      @livewire('hub.components.customers.table')
    </div>
</div>
