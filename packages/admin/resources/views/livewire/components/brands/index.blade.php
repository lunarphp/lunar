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

    <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading></x-hub::table.heading>
            <x-hub::table.heading sortable>
                {{ __('adminhub::global.name') }}
            </x-hub::table.heading>
            <x-hub::table.heading>
                {{ __('adminhub::components.brands.index.table_count_header_text') }}
            </x-hub::table.heading>
            <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
            @foreach ($brands as $brand)
                <x-hub::table.row>
                    <x-hub::table.cell class="w-24">
                        @if ($thumbnail = $brand->thumbnail)
                            <img class="rounded shadow"
                                 src="{{ $brand->thumbnail->getUrl('small') }}"
                                 loading="lazy" />
                        @else
                            <x-hub::icon ref="photograph"
                                         class="w-8 h-8 mx-auto text-gray-300" />
                        @endif
                    </x-hub::table.cell>
                    <x-hub::table.cell>{{ $brand->name }}</x-hub::table.cell>
                    <x-hub::table.cell>{{ $brand->products->count() }}</x-hub::table.cell>
                    <x-hub::table.cell>
                        <a href="{{ route('hub.brands.show', $brand->id) }}"
                           class="text-indigo-500 hover:underline">
                            {{ __('adminhub::components.brands.index.table_row_action_text') }}
                        </a>
                    </x-hub::table.cell>
                </x-hub::table.row>
            @endforeach
        </x-slot>
    </x-hub::table>
    <div>
        {{ $brands->links() }}
    </div>

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
                           wire:click.prevent="$set('showCreateForm', false)"
                           theme="gray">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>

            <x-hub::button type="submit">
                {{ __('adminhub::components.brands.index.create_brand') }}
            </x-hub::button>
        </x-slot>
    </x-hub::modal.dialog>
</div>
