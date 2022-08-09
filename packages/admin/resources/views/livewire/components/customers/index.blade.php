<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-xl font-bold md:text-2xl">
            {{ __('adminhub::catalogue.customers.index.title') }}
        </strong>
    </div>

    @livewire('hub.components.tables.customers-table', key())
</div>
