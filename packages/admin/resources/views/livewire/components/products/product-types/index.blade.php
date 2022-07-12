<div class="flex-col space-y-4">
    <div class="flex items-center justify-between">
        <strong class="text-xl font-bold md:text-2xl">
            {{ __('adminhub::catalogue.product-types.index.title') }}
        </strong>

        <div class="text-right">
            <x-hub::button tag="a"
                           href="{{ route('hub.product-type.create') }}">
                {{ __('adminhub::catalogue.product-types.index.create_btn') }}
            </x-hub::button>
        </div>
    </div>

    <div>
        <x-hub::table>
            <x-slot name="toolbar">
                <div class="p-4">
                    <x-hub::input.text wire:model="search"
                                       placeholder="Search by attribute or SKU"
                                       class="py-2" />
                </div>
            </x-slot>

            <x-slot name="head">
                <x-hub::table.heading>
                    {{ __('adminhub::global.name') }}
                </x-hub::table.heading>

                <x-hub::table.heading>
                    {{ __('adminhub::global.no_of_products') }}
                </x-hub::table.heading>

                <x-hub::table.heading>
                    {{ __('adminhub::global.no_of_attributes') }}
                </x-hub::table.heading>

                <x-hub::table.heading></x-hub::table.heading>
            </x-slot>

            <x-slot name="body">
                @forelse($productTypes as $type)
                    <x-hub::table.row>
                        <x-hub::table.cell>
                            {{ $type->name }}
                        </x-hub::table.cell>

                        <x-hub::table.cell>
                            {{ $type->products_count }}
                        </x-hub::table.cell>

                        <x-hub::table.cell>
                            {{ $type->mapped_attributes_count }}
                        </x-hub::table.cell>

                        <x-hub::table.cell>
                            <a href="{{ route('hub.product-type.show', $type->id) }}"
                               class="text-indigo-500 hover:underline">
                                Edit
                            </a>
                        </x-hub::table.cell>
                    </x-hub::table.row>
                @empty
                @endforelse
            </x-slot>
        </x-hub::table>
    </div>

    <div>
        {{ $productTypes->links() }}
    </div>
</div>
