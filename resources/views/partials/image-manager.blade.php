<div class="shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <header>
      <h3 class="text-lg font-medium leading-6 text-gray-900">
        {{ __('adminhub::partials.image-manager.heading') }}
      </h3>
    </header>

    <div>
      <x-hub::input.fileupload wire:model="{{ $wireModel }}" :filetypes="$filetypes" multiple />
    </div>
    @if($errors->has($wireModel.'*'))
      <x-hub::alert level="danger">{{ __('adminhub::partials.image-manager.generic_upload_error') }}</x-hub::alert>
    @endif

    <div>
      <div wire:sort sort.options='{group: "images", method: "sort"}' class="relative mt-4 space-y-2">
        @foreach($this->images as $image)
          <div
            class="flex items-center justify-between p-4 bg-white border rounded-md shadow-sm"
            sort.item="images"
            sort.id="{{ $image['sort_key'] }}"
            wire:key="image_{{ $image['sort_key'] }}"
          >
            <div class="flex items-center w-full space-x-6">
              @if(count($images) > 1)
                <div class="cursor-move" sort.handle>
                  <x-hub::icon ref="dots-vertical" style="solid" class="text-gray-400 cursor-grab" />
                </div>
              @endif

              <div>
                <button type="button" wire:click="$set('images.{{ $loop->index }}.preview', true)">
                  <img src="{{ $image['thumbnail'] }}" class="w-8 overflow-hidden rounded-md"/>
                </button>
                <x-hub::modal wire:model="images.{{ $loop->index }}.preview">
                  <img src="{{ $image['original'] }}">
                </x-hub::modal>
              </div>

              <div class="w-full">
                  <x-hub::input.text wire:model="images.{{ $loop->index }}.caption" placeholder="Enter Alt. text" />
              </div>

              <div class="flex items-center ml-4 space-x-4">
                <x-hub::tooltip text="Make primary">
                  <x-hub::input.toggle :disabled="$image['primary']" :on="$image['primary']" wire:click.prevent="setPrimary('{{ $loop->index }}')" />
                </x-hub::tooltip>

                @if(!empty($image['id']))
                  <x-hub::tooltip :text="__('adminhub::partials.image-manager.remake_transforms')">
                    <button wire:click.prevent="regenerateConversions('{{ $image['id'] }}')" href="{{ $image['original'] }}" type="button">
                      <x-hub::icon ref="refresh" style="solid" class="text-gray-400 hover:text-indigo-500 hover:underline" />
                    </button>
                  </x-hub::tooltip>
                @endif

                <button
                  type="button"
                  wire:click.prevent="removeImage('{{ $image['sort_key'] }}')"
                  class="text-gray-400 hover:text-red-500 "
                >
                  <x-hub::icon ref="trash" style="solid"/>
                </button>

              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
