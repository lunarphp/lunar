<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-xl font-bold md:text-2xl">
            {{ __('adminhub::catalogue.brands.index.title') }}
        </strong>


        <div class="text-right">
            <x-hub::button wire:click.prevent="addBrand">
                {{ __('adminhub::components.brands.index.create_brand') }}
            </x-hub::button>
        </div>
    </div>

    @livewire('hub.components.brands.table')

    <x-hub::modal.dialog wire:model="showCreateForm"
                         form="createBrand">
        <x-slot name="title">
            {{ __('adminhub::components.brands.index.create_brand') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <x-hub::input.group :label="__('adminhub::inputs.name')"
                                    for="name"
                                    :error="$errors->first('brand.name')"
                                    required>
                    <x-hub::input.text wire:model="brand.name"
                                       :error="$errors->first('brand.name')" />
                </x-hub::input.group>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-hub::button type="button"
                           wire:click.prevent="resetForm"
                           theme="gray">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>

            <x-hub::button type="submit">
                {{ __('adminhub::components.brands.index.create_brand') }}
            </x-hub::button>
        </x-slot>
    </x-hub::modal.dialog>
</div>
