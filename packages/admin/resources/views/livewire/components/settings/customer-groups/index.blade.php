<div class="space-y-4">
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.customer-groups.index.title') }}
        </h1>

        <div class="mt-4 sm:mt-0">
            <x-hub::button tag="a"
                           href="{{ route('hub.customer-groups.create') }}">
                {{ __('adminhub::settings.customer-groups.index.create_btn') }}
            </x-hub::button>
        </div>
    </header>

    <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading sortable>
                {{ __('adminhub::global.name') }}
            </x-hub::table.heading>
            <x-hub::table.heading>
                {{ __('Members') }}
            </x-hub::table.heading>
            <x-hub::table.heading>
                {{ __('adminhub::global.date') }}
            </x-hub::table.heading>
            <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
            @foreach ($customerGroups as $customerGroup)
                <x-hub::table.row>
                    <x-hub::table.cell>
                        {{ $customerGroup->translate('name') }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $customerGroup->customers->count() }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $customerGroup->created_at->format('d/m/Y') }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        <a href="{{ route('hub.customer-groups.show', $customerGroup->id) }}"
                           class="text-indigo-500 hover:underline">
                            {{ __('adminhub::settings.customer-groups.index.table_row_action_text') }}
                        </a>
                    </x-hub::table.cell>
                </x-hub::table.row>
            @endforeach
        </x-slot>
    </x-hub::table>
<div>
{{ $customerGroups->links() }}
    </div>
</div>
