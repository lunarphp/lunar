<div class="space-y-4">
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.languages.index.title') }}
        </h1>

        <div class="mt-4 sm:mt-0">
            <x-hub::button tag="a"
                           href="{{ route('hub.languages.create') }}">
                {{ __('adminhub::settings.languages.index.create_btn') }}
            </x-hub::button>
        </div>
    </header>

    <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading>Default</x-hub::table.heading>
            <x-hub::table.heading sortable>Name</x-hub::table.heading>
            <x-hub::table.heading>Code</x-hub::table.heading>
            <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
            @foreach ($languages as $language)
                <x-hub::table.row>
                    <x-hub::table.cell class="w-2">
                        <span
                              class="block w-2 h-2 border rounded-full @if ($language->default) bg-green-400 border-green-600 @endif"></span>
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $language->name }}
                    </x-hub::table.cell>
                    <x-hub::table.cell>
                        {{ $language->code }}
                    </x-hub::table.cell>
                    <x-hub::table.cell class="text-right">
                        <a href="{{ route('hub.languages.show', $language->id) }}"
                           class="text-indigo-500 hover:underline">
                            {{ __('adminhub::settings.languages.index.table_row_action_text') }}
                        </a>
                    </x-hub::table.cell>
                </x-hub::table.row>
            @endforeach
        </x-slot>
    </x-hub::table>
    <div>
        {{ $languages->links() }}
    </div>
</div>
