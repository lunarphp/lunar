
<x-filament-widgets::widget>

    <header class="fi-header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">


        <div>
            <h1 class="fi-header-heading text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl mb-4">
                Collections
            </h1>
        </div>
        <div class="float-right">
            <x-filament::button>
                New collection
            </x-filament::button>
        </div>
    </header>

    {{--
        - Add actions
        - Add sorting
        - Add expand/contract
        - Sort Tailwind CSS styles
    --}}

    <div
        class="space-y-2"
    >

    @foreach ($record->collections()->withCount('children')->whereIsRoot()->defaultOrder()->get() as $collection)
        <x-lunarpanel::collectiongroups.treeview-item
            :collection="$collection->translateAttribute('name')"
        />
    @endforeach

    </div>

</x-filament-widgets::widget>
