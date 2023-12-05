<div class="flex-col px-8 space-y-4 md:px-12">
  <div class="space-y-4">
    <form action="#" wire:submit.prevent="save" class="shadow sm:rounded-md">
      <div class="flex-col px-4 py-5 space-y-4 bg-white rounded-t sm:p-6">
        <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('list.name')">
          <x-hub::input.text wire:model.defer="list.name" name="name" id="name" :error="$errors->first('list.name')" />
        </x-hub::input.group>

        <div class="flex items-center justify-between">
          <strong>Products</strong>
          @livewire('hub.components.product-search', [
            'existing' => collect($this->products->pluck('purchasable')),
          ], key('product-search'))
        </div>

        <div class="mt-4 space-y-2">
          @foreach($this->products as $index => $product)
            <div class="flex items-center justify-between p-2 border rounded" wire:key="product_{{ $product['id'] }}">
              <div class="flex items-center">
                <img src="{{ $product['thumbnail'] }}" class="w-6 mr-3 rounded">
                {{ $product['name'] }}
              </div>
              <button type="button" wire:click="removeProduct({{ $index }})">
                <x-hub::icon ref="trash" class="w-4 text-gray-500 hover:text-red-500" />
              </button>
            </div>
          @endforeach
        </div>
      </div>
      <div class="px-4 py-3 text-right rounded-b bg-gray-50 sm:px-6">
        <x-hub::button type="submit">
          @if($list->id)
            Save exclusion list
          @else
            Create exclusion list
          @endif
        </x-hub::button>
      </div>
    </form>
  </div>
</div>