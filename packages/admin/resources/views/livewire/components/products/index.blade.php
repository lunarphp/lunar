<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-lg font-semibold md:text-2xl">
            {{ __('adminhub::components.products.index.title') }}
        </strong>

        <div class="text-right">
            <x-hub::button tag="a"
                           href="{{ route('hub.products.create') }}">
                {{ __('adminhub::components.products.index.create_product') }}</x-hub::button>
        </div>
    </div>

    @livewire('hub.components.products.table')
</div>
