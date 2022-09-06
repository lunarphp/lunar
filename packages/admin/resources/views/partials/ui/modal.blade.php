<x-hub::modal.dialog form="removeAddress"
                     wire:model="addressToRemove">
    <x-slot name="title">
        {{ __('adminhub::components.customers.show.remove_address.title') }}
    </x-slot>

    <x-slot name="content">
        <x-hub::alert level="warning">
            {{ __('adminhub::components.customers.show.remove_address.confirm') }}
        </x-hub::alert>
    </x-slot>

    <x-slot name="footer">
        <x-hub::button type="button"
                       wire:click.prevent="$set('addressToRemove', null)"
                       theme="gray">
            {{ __('adminhub::global.cancel') }}
        </x-hub::button>

        <x-hub::button type="submit">
            {{ __('adminhub::components.customers.show.remove_address_btn') }}
        </x-hub::button>
    </x-slot>
</x-hub::modal.dialog>
