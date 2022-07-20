<div class="space-y-4">
    <header class="sm:flex sm:justify-between sm:items-center">
        <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
            {{ $this->tabs->get($handle) }}
        </h1>

        <div class="mt-4 sm:mt-0 space-x-2">
            <x-hub::button wire:click.prevent="$set('showGroupCreate', true)">
                {{ __('Create New') }} {{ \Illuminate\Support\Str::singular($this->tabs->get($handle)) }}
            </x-hub::button>
            <x-hub::button wire:click.prevent="$set('showGroupAssign', true)" theme="green">
                {{ __('Assign') }} {{ \Illuminate\Support\Str::singular($this->tabs->get($handle)) }}
            </x-hub::button>
        </div>
    </header>


    <div wire:sort
         sort.options='{group: "groups", method: "sortGroups"}'
         class="space-y-2">
        @forelse($this->sortedGroups as $group)
            <div wire:key="group_{{ $group->id }}"
                 x-data="{ expanded: {{ $group->values->count() <= 4 ? 'true' : 'false' }} }"
                 sort.item="groups"
                 sort.id="{{ $group->id }}">
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

                    <div class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300">
                        <div class="flex items-center justify-between expand">
                            {{ $group->translate('name') }}
                        </div>
                        <div class="flex">
                            @if ($group->values->count())
                                <button @click="expanded = !expanded">
                                    <div class="transition-transform"
                                         :class="{
                                             '-rotate-90 ': expanded
                                         }">
                                        <x-hub::icon ref="chevron-left"
                                                     style="solid" />
                                    </div>
                                </button>
                            @endif
                            <x-hub::dropdown minimal>
                                <x-slot name="options">
                                    <x-hub::dropdown.button wire:click="$set('editGroupId', {{ $group->id }})"
                                                            class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 border-b hover:bg-gray-50">
                                        {{ __('adminhub::components.attributes.show.edit_group_btn') }}
                                    </x-hub::dropdown.button>

                                    <x-hub::dropdown.button wire:click="$set('attributeCreateGroupId', {{ $group->id }})"
                                                            class="flex items-center justify-between px-4 py-2 text-sm border-b hover:bg-gray-50">
                                        {{ __('adminhub::components.attributes.show.create_attribute') }}
                                    </x-hub::dropdown.button>

                                    <x-hub::dropdown.button wire:click="$set('deleteGroupId', {{ $group->id }})"
                                                            class="flex items-center justify-between px-4 py-2 text-sm border-b hover:bg-gray-50">
                                        <span
                                                class="text-red-500">{{ __('adminhub::components.attributes.show.delete_group_btn') }}</span>
                                    </x-hub::dropdown.button>
                                </x-slot>
                            </x-hub::dropdown>
                        </div>
                    </div>
                </div>
                <div class="py-4 pl-2 pr-4 mt-2 space-y-2 bg-black border-l rounded bg-opacity-5 ml-7"
                     @if ($group->values->count()) x-show="expanded" @endif>
                    <div class="space-y-2"
                         wire:sort
                         sort.options='{group: "attributes", method: "sortGroupValues", owner: {{ $group->id }}}'
                         x-show="expanded">
                        @foreach ($group->values as $attribute)
                            <div class="flex items-center justify-between w-full p-3 text-sm bg-white border border-transparent rounded shadow-sm sort-item-element hover:border-gray-300"
                                 wire:key="attribute_{{ $attribute->id }}"
                                 sort.item="attributes"
                                 sort.parent="{{ $group->id }}"
                                 sort.id="{{ $attribute->id }}">
                                <div sort.handle
                                     class="cursor-grab">
                                    <x-hub::icon ref="selector"
                                                 style="solid"
                                                 class="mr-2 text-gray-400 hover:text-gray-700" />
                                </div>
                                <span class="truncate grow">{{ $attribute->translate('name') }}</span>
                                <div class="mr-4 text-xs text-gray-500">
                                    {{ class_basename($attribute->type) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if (!$group->values->count())
                        <span class="mx-4 text-sm text-gray-500">
                            {{ __('adminhub::components.attributes.show.no_attributes_text') }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="w-full text-center text-gray-500">
                {{ __('adminhub::components.attributes.show.no_groups') }}
            </div>
        @endforelse
    </div>
</div>
