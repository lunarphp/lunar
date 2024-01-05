@props(['id', 'name', 'childrenCount', 'children', 'parent', 'editUrl', 'thumbnail' => null])
@php
    $actions = [];
    $collectionGroup = $this->record;

    foreach ($this->getTreeActions() as $action) {
        $actions[] = $action(['id' => $id, 'collection_group_id' => $collectionGroup->id]);
    }
@endphp
<div class="relative group">
    <div @class([
        'w-full flex justify-between rounded'
    ])>
        <div class="flex w-full space-x-2">
            <div @class([
                'flex items-center text-gray-400 hover:text-gray-500',
                'cursor-grab',
            ]) x-sortable-handle>
                <x-filament::icon alias="lunar::reorder" class="w-5 h-5" />
            </div>

            <div class="flex grow bg-white border-gray-600 dark:bg-gray-900 shadow-sm rounded">
                    <button
                            class="px-3 text-gray-500 appearance-none"
                            type="button"
                            title=""
                            wire:click.prevent="toggleChildren('{{ $id }}')"
                            @disabled(!$childrenCount)
                    >
                        <x-filament::icon
                                alias="lunar::chevron-right"
                                @class([
                                    'w-3.5 h-3.5 transition ease-in-out duration-200 rtl:rotate-180',
                                    'ltr:rotate-90 rtl:!rotate-90' => !!count($children),
                                    'opacity-0' => !$childrenCount
                                ])
                        />
                    </button>

                <button
                        @class([
                            'w-full py-2 text-left rtl:text-right appearance-none',
                            'px-4' => false,
                            'cursor-default' => true,
                        ])
                        type="button"
                >
                    <div class="flex items-center space-x-2">
                        <div>
                            @if($thumbnail)
                                <img src="{{ $thumbnail }}" class="w-10 border border-gray-100 dark:border-gray-600 rounded p-0.5">
                            @else
                                <x-filament::icon alias="lunar::image-placeholder" class="p-1 text-gray-200 w-10 h-10" />
                            @endif
                        </div>
                        <x-filament::link :href="$editUrl" class="text-base pl-2">{{ $name }}</x-filament::link>
                    </div>
                </button>

                <div class="flex items-center flex-shrink-0 px-2 space-x-2 rtl:space-x-reverse">
                    <x-filament-actions::group
                            :actions="$actions"
                            label="Actions"
                            icon="heroicon-m-ellipsis-vertical"
                            color="primary"
                            size="md"
                            tooltip="More actions"
                            dropdown-placement="bottom-start"
                    />
                </div>
            </div>
        </div>
    </div>


    @if (count($children))
        <div style="margin-left:40px;" class="my-2 space-y-2">
            <x-lunarpanel::collections.nested-set-tree :nodes="$children" :group="'parent_id_'.$parent"/>
        </div>
    @endif
</div>