<div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
        <header>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                Limitations
            </h3>
        </header>

        <div class="space-y-4">

            <x-hub::input.group
                label="Limit by collection"
                for="brands"
            >

            <div class="rounded border h-full overflow-y-scroll max-h-96 bg-gray-50 px-2">
                @foreach($this->collectionTree as $collectionNode)
                    @include('adminhub::partials.forms.discount.collection-tree-node', [
                        'node' => $collectionNode,
                    ])
                @endforeach
            </div>

            </x-hub::input.group>

            <x-hub::input.group
                label="Limit by brand"
                for="brands"
            >
              <div class="rounded border h-full overflow-y-scroll max-h-96 p-2 bg-gray-50 space-y-2">
                @foreach($this->brands as $brand)
                    <label class="flex items-center space-x-2  bg-white py-2 rounded shadow text-sm px-3 cursor-pointer hover:bg-gray-50" wire:key="av_brand_{{ $brand->id }}">
                      <input type="checkbox" wire:model="selectedBrands" value="{{ $brand->id }}">
                      <div>{{ $brand->name }}</div>
                    </label>
                @endforeach
              </div>
            </x-hub::input.group>
        </div>
    </div>
</div>
