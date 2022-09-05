<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-xl font-bold md:text-2xl">
            {{ __('adminhub::catalogue.customers.index.title') }}
        </strong>
    </div>

    <div class="space-y-4">
      @livewire('hub.components.customers.table')
        <x-hub::table>
            <x-slot name="toolbar">
                <div class="p-4 space-y-4 border-b">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center w-full space-x-4">
                            <x-hub::input.text :placeholder="__('adminhub::catalogue.customers.index.placeholder')"
                                               class="py-2"
                                               wire:model.debounce.400ms="search" />
                        </div>
                    </div>
                </div>
            </x-slot>

            <x-slot name="head">
                <x-hub::table.heading>
                    {{ __('adminhub::global.name') }}
                </x-hub::table.heading>

                <x-hub::table.heading>
                    {{ __('adminhub::global.company_name') }}
                </x-hub::table.heading>

                <x-hub::table.heading>
                    {{ __('adminhub::global.vat_no') }}
                </x-hub::table.heading>

                @foreach ($this->attributes as $attribute)
                    <x-hub::table.cell>
                        {{ $attribute->translate('name') }}
                    </x-hub::table.cell>
                @endforeach

                <x-hub::table.heading></x-hub::table.heading>
            </x-slot>

            <x-slot name="body">
                @foreach ($this->customers as $customer)
                    <x-hub::table.row>
                        <x-hub::table.cell>
                            {{ $customer->fullName }}
                        </x-hub::table.cell>

                        <x-hub::table.cell>
                            {{ $customer->company_name }}
                        </x-hub::table.cell>

                        <x-hub::table.cell>
                            {{ $customer->vat_no }}
                        </x-hub::table.cell>

                        @foreach ($this->attributes as $attribute)
                            <x-hub::table.cell>
                                {{ $customer->translateAttribute($attribute->handle) }}
                            </x-hub::table.cell>
                        @endforeach

                        <x-hub::table.cell>
                            <a href="{{ route('hub.customers.show', $customer->id) }}"
                               class="text-indigo-500 hover:underline">
                                {{ __('adminhub::global.view') }}
                            </a>
                        </x-hub::table.cell>
                    </x-hub::table.row>
                @endforeach
            </x-slot>
        </x-hub::table>

        <div>
            {{ $this->customers->links() }}
        </div>
    </div>
</div>
