<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::menu.product.image') }}
      </h3>
    </header>
    <div class="grid grid-cols-2 gap-4">
      <div>
        @if($this->thumbnail)
          <div class="space-y-4">
            <img src="{{ $this->thumbnail }}" class="rounded shadow-sm">
          </div>
        @elseif ($this->existingThumbnail && !$removeImage)
          <div class="space-y-4">
            <img src="{{ $this->existingThumbnail }}" class="rounded shadow-sm">
          </div>
        @else
        <figure class="inline-flex items-center justify-center w-full h-64 bg-gray-100 rounded">
          <x-hub::icon ref="photograph" class="w-32 h-32 text-gray-200" />
        </figure>
        @endif
      </div>
      <div class="space-y-4">
        <x-hub::input.fileupload wire:model="image" :filetypes="['image/*']" />

        <div>
          <div class="flex space-x-4">
            @if($this->thumbnail)
              <x-hub::button type="button" theme="danger" wire:click.prevent="$set('image', null)">
                {{ __('adminhub::global.remove') }}
              </x-hub::button>
            @elseif ($this->existingThumbnail)
                <x-hub::button
                  type="button"
                  theme="danger"
                  wire:click.prevent="$set('removeImage', {{ !$removeImage }})"
                >
                  {{ $removeImage ? 'Undo' : 'Remove' }}
                </x-hub::button>
            @endif
            <x-hub::button theme="gray" type="button" wire:click="$set('showImageSelectModal', true)">{{ __('adminhub::menu.product.choose-existing-btn') }}</x-hub::button>
          </div>
          <x-hub::modal.dialog wire:model="showImageSelectModal">
            <x-slot name="title">
                {{ __('adminhub::menu.product.select-product-image') }}
            </x-slot>
            <x-slot name="content">
              <div class="grid grid-cols-4 gap-4 overflow-y-auto max-h-96">
                @forelse($product->media as $productImage)
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
