<div>
    <div class="grid grid-cols-6 gap-4 p-2 border border-b-0 rounded-t">
        <div class="col-span-4">
            <x-hub::input.text
                wire:model.debounce="searchTerm"
                placeholder="Search by collection name"

            />
        </div>
        <div class="col-span-2">
            <x-hub::input.select for="group" wire:model="collectionGroupId" :disabled="!!$this->searchTerm || $showOnlySelected">
                @foreach($this->collectionGroups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </x-hub::input.select>
        </div>
    </div>
    <div class="rounded-b border h-full overflow-y-scroll max-h-96 bg-gray-50">
        @if(count($selectedCollections))
            <div class="bg-gray-100 py-1 px-2 text-sm border-b text-gray-700">
                {{ count($selectedCollections) }} selected,
                <a href="#" class="text-blue-600 hover:underline" wire:click.prevent="toggleSelected">
                    @if($showOnlySelected) show all @endif
                    @if(!$showOnlySelected) show selected @endif
                </a>
            </div>
        @endif
        <div class="px-2">
            @foreach($this->collections as $collectionNode)
                @include('adminhub::partials.collections.collection-tree-node', [
                    'node' => $collectionNode,
                ])
            @endforeach
        </div>
    </div>
</div>
