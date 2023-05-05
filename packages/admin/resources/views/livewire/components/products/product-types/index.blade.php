<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-xl font-semibold md:text-2xl">
            {{ __('adminhub::catalogue.product-types.index.title') }}
        </strong>

        <div class="text-right">
            <x-hub::button tag="a"
                           href="{{ route('hub.product-types.create') }}">
                {{ __('adminhub::catalogue.product-types.index.create_btn') }}
            </x-hub::button>
        </div>
    </div>

    <div>
        @livewire('hub.components.products.product-types.table')
    </div>

</div>
