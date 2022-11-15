<div class="space-y-2 my-2">
    <label class="flex items-center space-x-2  bg-white py-2 rounded shadow text-sm px-3 cursor-pointer hover:bg-gray-50">
        <input type="checkbox" wire:model="selectedCollections" value="{{ $node->id }}">
        <div>
            {{ $node->attr('name') }}
        </div>
    </label>
    @if($node->children->count())
        @if(in_array($node->id, $selectedCollections))
            <div>
                <x-hub::alert>
                    This will apply to all children and descendants of this collection.
                </x-hub::alert>
            </div>
        @else
        <div class="space-y-2 ml-4">
            @foreach($node->children as $childNode)
                @include('adminhub::partials.forms.discount.collection-tree-node', [
                    'node' => $childNode
                ])
            @endforeach
        </div>
        @endif
    @endif
</div>
