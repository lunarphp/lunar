<div class="space-y-4">
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.currencies.index.title') }}
        </h1>

        <div class="mt-4 sm:mt-0">
            <x-hub::button tag="a"
                           href="{{ route('hub.currencies.create') }}">
                {{ __('adminhub::settings.currencies.index.create_currency_btn') }}
            </x-hub::button>
        </div>
    </header>

    <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading>
                {{ __('adminhub::global.default') }}
            </x-hub::table.heading>
            <x-hub::table.heading sortable>
                {{ __('adminhub::global.name') }}
            </x-hub::table.heading>
            <x-hub::table.heading>
                {{ __('adminhub::global.code') }}
            </x-hub::table.heading>
            <x-hub::table.heading>
                {{ __('adminhub::global.exchange_rate') }}
            </x-hub::table.heading>
            <x-hub::table.heading>
                {{ __('adminhub::global.enabled') }}
            </x-hub::table.heading>
            <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
            @forelse($currencies as $currency)
                <x-hub::table.row>
                    <x-hub::table.cell>
                        <span
                              class="block w-2 h-2 border rounded-full @if ($currency->default) bg-green-400 border-green-600 @endif"></span>
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $currency->name }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $currency->code }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $currency->exchange_rate }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        <x-hub::icon :ref="$currency->enabled ? 'check' : 'x'"
                                     :class="$currency->enabled ? 'text-green-500' : 'text-red-500'"
                                     style="solid" />
                    </x-hub::table.cell>
                    <x-hub::table.cell class="text-right">
                        <a href="{{ route('hub.currencies.show', $currency->id) }}"
                           class="text-indigo-500 hover:underline">
                            {{ __('adminhub::settings.currencies.index.table_row_action_text') }}
                        </a>
                    </x-hub::table.cell>
                </x-hub::table.row>
            @empty
                <x-hub::table.no-results>
                    {{ __('adminhub::settings.currencies.index.no_results') }}
                </x-hub::table.no-results>
            @endforelse
        </x-slot>
    </x-hub::table>
    <div>
        {{ $currencies->links() }}
    </div>
</div>
