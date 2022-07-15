<div class="space-y-4">
    <header>
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ __('adminhub::settings.tags.index.title') }}
        </h1>
    </header>

    <x-hub::table>
        <x-slot name="head">
            <x-hub::table.heading>Value</x-hub::table.heading>
            <x-hub::table.heading></x-hub::table.heading>
        </x-slot>
        <x-slot name="body">
            @foreach ($tags as $tag)
                <x-hub::table.row>
                    <x-hub::table.cell>
                        {{ $tag->value }}
                    </x-hub::table.cell>
                    <x-hub::table.cell class="text-right">
                        <a href="{{ route('hub.tags.show', $tag->id) }}"
                           class="text-indigo-500 hover:underline">
                            {{ __('adminhub::settings.tags.index.table_row_action_text') }}
                        </a>
                    </x-hub::table.cell>
                </x-hub::table.row>
            @endforeach
        </x-slot>
    </x-hub::table>
    <div>
        {{ $tags->links() }}
    </div>
</div>
