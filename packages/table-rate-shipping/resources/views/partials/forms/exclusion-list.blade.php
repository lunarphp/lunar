<div class="flex-col space-y-4 bg-white rounded-t">
  <div class="px-4 py-5 sm:p-6">
    <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('list.name')">
      <x-hub::input.text wire:model.defer="list.name" name="name" id="name" :error="$errors->first('list.name')" />
    </x-hub::input.group>
  </div>

  <div class="flex items-center justify-between px-4 sm:px-6">
    <strong>Products</strong>
    @livewire('hub.components.product-search', [
      'existing' => collect($this->products->pluck('purchasable')),
    ], key('product-search'))
  </div>

  <div class="mt-4 space-y-2 max-h-96 overflow-y-auto px-6 py-3 bg-gray-50">
    @foreach($this->products as $index => $product)
      <div class="flex items-center justify-between shadow-sm bg-white p-2 border rounded" wire:key="product_{{ $product['id'] }}">
        <div class="flex items-center">
          <img src="{{ $product['thumbnail'] }}" class="w-6 mr-3 rounded">
          <div class="text-sm">
            {{ $product['name'] }}
          </div>
        </div>
        <button type="button" wire:click="removeProduct({{ $index }})">
          <x-hub::icon ref="trash" class="w-4 text-gray-500 hover:text-red-500" />
        </button>
      </div>
    @endforeach
  </div>
</div>
<div class="px-4 py-3 flex justify-between rounded-b bg-gray-100 sm:px-6">
  <x-hub::button type="button" wire:click="$set('showRemoveModal', true)" theme="danger">
    Remove list
  </x-hub::button>
  <x-hub::button type="submit">
    @if($list->id)
      Save exclusion list
    @else
      Create exclusion list
    @endif
  </x-hub::button>
</div>
