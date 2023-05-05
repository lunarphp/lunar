
<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-lg font-semibold md:text-2xl">
            {{ __('adminhub::components.discounts.index.title') }}
        </strong>

        <div class="text-right">
            <x-hub::button tag="a"
                           href="{{ route('hub.discounts.create') }}">
                {{ __('adminhub::components.discounts.index.create_discount') }}</x-hub::button>
        </div>
    </div>

    @livewire('hub.components.discounts.table')
</div>
