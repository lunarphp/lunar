<div class="space-y-2"
     wire:sort
     sort.options='{group: "{{ $sortGroup }}", method: "sort"}'>
    @foreach ($nodes as $node)
        <div wire:key="node_{{ $node['id'] }}"
             sort.item="{{ $sortGroup }}"
             sort.id="{{ $node['id'] }}"
             @if ($node['parent_id']) sort.parent="{{ $node['parent_id'] }}" @endif>
            <div class="flex items-center gap-2">
                <div wire:loading
                     wire:target="sort">
                    <x-hub::icon ref="refresh"
                                 style="solid"
                                 class="w-5 text-gray-400 transition rotate-180 dark:text-gray-300 animate-spin" />
                </div>

                <div wire:loading.remove
                     wire:target="sort">
                    <div sort.handle
                         class="cursor-grab">
                        <x-hub::icon ref="selector"
                                     style="solid"
                                     class="text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200" />
                    </div>
                </div>

                <div
                     class="flex items-center justify-between w-full p-3 text-sm transition bg-white border border-white rounded shadow-sm dark:bg-gray-800 dark:border-gray-700 sort-item-element hover:border-gray-100 dark:hover:border-gray-600">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center">
                            @if ($node['thumbnail'])
                                <img class="w-6 rounded"
                                     src="{{ $node['thumbnail'] }}" />
                            @else
                                <x-hub::icon ref="photograph"
                                             class="w-6 mx-auto text-gray-400 dark:text-gray-300" />
                            @endif

                            <p class="ml-2 text-gray-900 dark:text-white">
                                {{ $node['name'] }}
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            @if ($node['children_count'])
                                <div class="text-sm text-gray-400 dark:text-gray-300">
                                    {{ $node['children_count'] }}
                                </div>
                            @endif

                            @if ($node['children_count'])
                                <button type="button"
                                        wire:click.prevent="toggle({{ $node['id'] }})"
                                        class="text-gray-500 transition dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <div class="transition"
                                         :class="{ '-rotate-90 ': {{ count($node['children']) }} }">
                                        <x-hub::icon ref="chevron-left"
                                                     style="solid" />
                                    </div>
                                </button>
                            @endif

                            <x-hub::dropdown minimal>
                                <x-slot name="options">
                                    <x-hub::dropdown.link :href="route('hub.collections.show', [
                                        'group' => $owner,
                                        'collection' => $node['id'],
                                    ])"
                                                          class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                                        {{ __('adminhub::catalogue.collections.groups.node.edit') }}
                                        <x-hub::icon ref="pencil"
                                                     style="solid"
                                                     class="w-4" />
                                    </x-hub::dropdown.link>

                                    @if ($node['parent_id'])
                                        <x-hub::dropdown.button wire:click.prevent="moveToRoot('{{ $node['id'] }}')">
                                            {{ __('adminhub::catalogue.collections.groups.node.make_root') }}
                                        </x-hub::dropdown.button>
                                    @endif

                                    <x-hub::dropdown.button wire:click.prevent="moveCollection('{{ $node['id'] }}')">
                                        {{ __('adminhub::catalogue.collections.groups.node.move') }}
                                    </x-hub::dropdown.button>

                                    <x-hub::dropdown.button wire:click.prevent="addCollection('{{ $node['id'] }}')">
                                        {{ __('adminhub::catalogue.collections.groups.node.add_child') }}
                                    </x-hub::dropdown.button>

                                    <x-hub::dropdown.button
                                                            wire:click.prevent="removeCollection('{{ $node['id'] }}')">
                                        {{ __('adminhub::catalogue.collections.groups.node.delete') }}
                                    </x-hub::dropdown.button>
                                </x-slot>
                            </x-hub::dropdown>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($node['children']))
                <div class="my-4 ml-8 space-y-2">
                    @livewire(
                        'hub.components.collections.collection-tree',
                        [
                            'nodes' => $node['children'],
                            'sortGroup' => 'children_' . $node['id'],
                            'owner' => $owner,
                        ],
                        key('tree-' . $node['id']),
                    )
                </div>
            @endif
        </div>
    @endforeach
</div>
