<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::menu.product.basic-information') }}
      </h3>
    </header>
    <div class="grid grid-cols-2 gap-4">
      <div class="space-y-4">

        @foreach($variant->values as $value)
          <x-hub::input.group :label="$value->option->name->en" for="sku">
            {{ $value->name->en }}
          </x-hub::input.group>
        @endforeach
      </div>
      <div class="space-y-4">
        <x-hub::input.fileupload wire:model="image" />
        @if($this->thumbnail)
          <img src="{{ $this->thumbnail }}">
        @elseif ($this->existingThumbnail)
          <img src="{{ $this->existingThumbnail }}">
        @endif
        <div>
          <x-hub::button theme="gray" type="button" wire:click="$set('showImageSelectModal', true)">{{ __('adminhub::menu.product.choose-existing-btn') }}</x-hub::button>

          <x-hub::modal.dialog wire:model="showImageSelectModal">
            <x-slot name="title">
                {{ __('adminhub::menu.product.select-product-image') }}
            </x-slot>
            <x-slot name="content">
              <div class="grid grid-cols-3 gap-4">
                @forelse($product->images as $productImage)
                  <label class="cursor-pointer">
                    <input wire:model="imageToSelect" name="imageToSelect" value="{{ $productImage->id }}" class="sr-only peer" type="radio">
                    <img src="{{ $productImage->getFullUrl('small') }}" class="border-2 border-transparent rounded-lg shadow-sm peer-checked:border-blue-500">
                  </label>
                @empty
                  <div class="col-span-3">
                    <x-hub::alert>{{ __('adminhub::notifications.product.no-images-associated') }}</x-hub::alert>
                  </div>
                @endforelse
              </div>
            </x-slot>
            <x-slot name="footer">
              <div class="flex justify-end space-x-4">
                <x-hub::button type="button" theme="gray" wire:click="$set('showImageSelectModal', false)">{{ __('adminhub::global.cancel') }}</x-hub::button>
                <x-hub::button
                  type="button"
                  :disabled="!$imageToSelect"
                  wire:click.prevent="selectImage"
                >
                {{ __('adminhub::menu.product.choose-image') }}
                </x-hub::button>
              </div>
            </x-slot>

          </x-hub::modal.dialog>
        </div>
      </div>

    </div>
  </div>
</div>
