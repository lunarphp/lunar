<div class="space-y-2"
     wire:sort
     sort.options='{group: "{{ $sortGroup }}", method: "sort"}'>
    @foreach ($nodes as $node)
        <div wire:key="node_{{ $node['id'] }}"
             sort.item="{{ $sortGroup }}"
             sort.id="{{ $node['id'] }}"
             @if ($node['parent_id']) sort.parent="{{ $node['parent_id'] }}" @endif>
            <div class="flex items-center">
                <div wire:loading
                     wire:target="sort">
                    <x-hub::icon ref="refresh"
                                 style="solid"
                                 class="w-5 mr-2 text-gray-300 rotate-180 animate-spin" />
                </div>

                <div wire:loading.remove
                     wire:target="sort">
                    <div sort.handle
                         class="cursor-grab">
                        <x-hub::icon ref="selector"
                                     style="solid"
                                     class="mr-2 text-gray-400 hover:text-gray-700" />
                    </div>
                </div>

                <div
                     class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center">
                            @if ($node['thumbnail'])
                                <img class="w-6 rounded"
                                     src="{{ $node['thumbnail'] }}" />
                            @else
                                <x-hub::icon ref="photograph"
                                             class="w-6 mx-auto text-gray-300" />
                            @endif
                            <div class="ml-2 truncate">{{ $node['name'] }}</div>
                        </div>

                        @if ($node['children_count'])
                            <div class="text-sm text-gray-400 w-18">{{ $node['children_count'] }}</div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end w-16">
                        @if ($node['children_count'])
                            <button type="button"
                                    wire:click.prevent="toggle({{ $node['id'] }})">
                                <div class="transition-transform"
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

                                <x-hub::dropdown.button wire:click.prevent="removeCollection('{{ $node['id'] }}')">
                                    {{ __('adminhub::catalogue.collections.groups.node.delete') }}
                                </x-hub::dropdown.button>
                            </x-slot>
                        </x-hub::dropdown>
                    </div>
                </div>
            </div>

            @if (count($node['children']))
                <div class="pl-4 my-4 space-y-2">
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
