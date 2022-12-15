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

    @livewire('hub.components.settings.currencies.table')
</div>
