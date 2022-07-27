<div>
    <div wire:loading
         wire:target="deleteGroup">
        {{ __('adminhub::global.deleting') }}
    </div>

    <div wire:loading.remove
         wire:target="deleteGroup">
        <div class="flex items-center justify-between">
            <div class="w-full">
                <input wire:model.lazy="group.name"
                       @class([
                           'w-full px-3 py-2 bg-transparent border border-dashed border-gray-300 rounded',
                           'border-red-500' => $errors->first('group.name'),
                           'hover:border-gray-400' => !$errors->first('group.name'),
                       ]) />

                <span class="text-sm text-red-500">
                    {{ $errors->first('group.name') }}
                </span>
            </div>

            <div class="ml-4 w-80">
                <div class="flex justify-end w-full space-x-4">
                    <x-hub::button wire:click.prevent="addCollection">
                        {{ __('adminhub::catalogue.collections.groups.add_collection_btn') }}
                    </x-hub::button>

                    <button type="button"
                            class="text-gray-400 hover:text-red-600"
                            wire:click.prevent="$set('showDeleteConfirm', true)">

                        <x-hub::icon ref="trash"
                                     class="w-4" />
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <x-hub::modal.dialog wire:model="showDeleteConfirm"
                                 form="deleteGroup">
                <x-slot name="title">
                    {{ __('adminhub::catalogue.collections.groups.delete.title') }}
                </x-slot>
                <x-slot name="content">
                    <p>{{ __('adminhub::catalogue.collections.groups.delete.strapline') }}
                    <p>
                    <p>{{ __('adminhub::catalogue.collections.groups.delete.warning') }}</p>
                </x-slot>
                <x-slot name="footer">
                    <x-hub::button type="button"
                                   wire:click.prevent="$set('showDeleteConfirm', false)"
                                   theme="gray">
                        {{ __('adminhub::global.cancel') }}
                    </x-hub::button>
                    <x-hub::button type="submit"
                                   theme="danger">
                        {{ __('adminhub::catalogue.collections.groups.delete.btn') }}
                    </x-hub::button>
                </x-slot>
            </x-hub::modal.dialog>
            <x-hub::modal.dialog wire:model="showCreateForm"
                                 form="createCollection">
                <x-slot name="title">
                    @if ($this->collectionParent)
                        {{ __('adminhub::catalogue.collections.create.child.title', [
                            'parent' => $this->collectionParent->translateAttribute('name'),
                        ]) }}
                    @else
                        {{ __('adminhub::catalogue.collections.create.root.title') }}
                    @endif
                </x-slot>
                <x-slot name="content">
                    <div class="space-y-4">
                        <x-hub::input.group :label="__('adminhub::inputs.name')"
                                            for="name"
                                            :error="$errors->first('collection.name')"
                                            required>
                            <x-hub::input.text wire:model="collection.name"
                                               :error="$errors->first('collection.name')" />
                        </x-hub::input.group>

                        @if ($this->slugIsRequired)
                            <x-hub::input.group :label="__('adminhub::inputs.slug.label')"
                                                for="slug"
                                                :error="$errors->first('slug')"
                                                required>
                                <x-hub::input.text wire:model.lazy="slug"
                                                   :error="$errors->first('slug')" />
                            </x-hub::input.group>
                        @endif
                    </div>
                </x-slot>
                <x-slot name="footer">
                    <x-hub::button type="button"
                                   wire:click.prevent="$set('showCreateForm', false)"
                                   theme="gray">
                        {{ __('adminhub::global.cancel') }}
                    </x-hub::button>
                    <x-hub::button type="submit">
                        {{ __('adminhub::catalogue.collections.create.btn') }}
                    </x-hub::button>
                </x-slot>
            </x-hub::modal.dialog>

            <x-hub::modal.dialog wire:model="collectionMove.source">
                <x-slot name="title">
                    {{ __('adminhub::catalogue.collections.groups.move.title') }}
                </x-slot>
                <x-slot name="content">
                    <div class="relative">
                        <x-hub::input.text wire:model="searchTerm"
                                           :placeholder="__(
                                               'adminhub::catalogue.collections.groups.move.search_placeholder',
                                           )" />
                        @if ($showCollectionSearchResults)
                            <div class="absolute w-full overflow-y-scroll bg-white rounded-b shadow max-h-64">
                                @foreach ($this->searchedCollections as $collection)
                                    <button class="block w-full px-4 py-2 text-sm text-left hover:bg-gray-100"
                                            type="button"
                                            wire:click.prevent="setMoveTarget('{{ $collection->id }}')">
                                        <span class="text-gray-500">
                                            {{ $collection->group->name }}
                                        </span>

                                        <x-hub::icon ref="chevron-right"
                                                     class="w-4 h-4 text-gray-500"
                                                     style="solid" />

                                        {{ $collection->translateAttribute('name') }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if ($this->sourceCollection && $this->targetCollection)
                        <div class="mt-4">
                            <x-hub::alert>
                                {{ __('adminhub::catalogue.collections.groups.move.alert', [
                                    'source' => $this->sourceCollection->translateAttribute('name'),
                                    'target' => $this->targetCollection->translateAttribute('name'),
                                ]) }}
                            </x-hub::alert>
                        </div>
                    @endif
                </x-slot>
                <x-slot name="footer">
                    <x-hub::button type="button"
                                   wire:click="moveCollection"
                                   :disabled="!$this->targetCollection">
                        {{ __('adminhub::catalogue.collections.groups.move.btn') }}
                    </x-hub::button>
                </x-slot>
            </x-hub::modal.dialog>

            @if ($this->collectionToRemove)
                <x-hub::modal.dialog wire:model="collectionToRemoveId"
                                     form="deleteCollection">
                    <x-slot name="title">
                        {{ __('adminhub::catalogue.collections.delete.title') }}
                    </x-slot>
                    <x-slot name="content">
                        @if ($childCount = $this->collectionToRemove->children->count())
                            <x-hub::alert level="danger">
                                {{ __('adminhub::catalogue.collections.delete.child.warning', [
                                    'count' => $childCount,
                                ]) }}
                            </x-hub::alert>
                        @else
                            <p>{{ __('adminhub::catalogue.collections.delete.root.warning') }}</p>
                        @endif
                    </x-slot>
                    <x-slot name="footer">
                        <div class="flex justify-between">
                            <x-hub::button type="button"
                                           wire:click.prevent="$set('collectionToRemoveId', null)"
                                           theme="gray">
                                {{ __('adminhub::global.cancel') }}
                            </x-hub::button>
                            <x-hub::button type="submit"
                                           theme="danger">
                                {{ __('adminhub::catalogue.collections.delete.btn') }}
                            </x-hub::button>
                        </div>
                    </x-slot>
                </x-hub::modal.dialog>
            @endif
            <div class="mt-4 space-y-2">
                @livewire(
                    'hub.components.collections.collection-tree',
                    [
                        'nodes' => $tree,
                        'sortGroup' => 'root',
                        'owner' => $group,
                    ],
                    key('tree-root'),
                )
            </div>
        </div>
    </div>
</div>
