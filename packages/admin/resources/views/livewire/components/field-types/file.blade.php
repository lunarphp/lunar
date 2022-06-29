<div>
  <div class="flex justify-end w-full mb-4">
    <x-hub::button wire:click="$set('showUploader', true)">
      Choose assets
    </x-hub::button>
  </div>

  <div class="grid grid-cols-4 gap-4">
    @foreach ($this->selectedModels as $index => $assetModel)
      <div class="flex items-center border rounded" wire:key="initial_asset_{{ $assetModel->id }}">
        <div>
          @if($assetModel->file->hasGeneratedConversion('small'))
            <img src="{{ $assetModel->file->getUrl('small') }}" class="w-12  pl-3 rounded block">
          @else
            <x-hub::icon ref="document" class="w-10 h-10 p-1 text-gray-300" />
          @endif
        </div>

        <div class="px-3 grow">
          <span class="block truncate text-xs">{{ $assetModel->file->file_name }}</span>
        </div>

        <div>
          <button
            type="button"
            wire:click="removeSelected({{ $assetModel->id }})"
            class="text-xs px-2 mt-2">
            <x-hub::icon ref="x" class="w-4" />
          </button>
        </div>
      </div>
    @endforeach
  </div>

  <x-hub::modal.dialog wire:model="showUploader">
    <x-slot name="title">
      Select assets
    </x-slot>
    <x-slot name="content">
      <div class="space-y-4">
        <input type="file" wire:model="file" />

        <div class="border-t pt-4">
          <div class="grid grid-cols-4 items-center gap-4">
            @forelse($this->assets as $asset)
              {{-- @if(!str_starts_with('image', $media->mime_type)) --}}
                <label
                  @class([
                    'border rounded text-center cursor-pointer  p-4',
                    'border-blue-500' => in_array($asset->id, $selected)
                  ])>
                  @if($asset->file->hasGeneratedConversion('small'))
                    <img src="{{ $asset->file->getUrl('small') }}" class="w-24 mx-auto rounded">
                  @else
                    <div>
                      <x-hub::icon ref="document" class="mx-auto" />
                    </div>
                  @endif

                  <span class="block truncate text-xs">{{ $asset->file->file_name }}</span>
                  <input
                    wire:model="selected"
                    type="checkbox"
                    class="hidden"
                    value="{{ $asset->id }}"
                  />
                </label>
              {{-- @endif --}}
            @empty
              Files you upload will appear here.
            @endforelse
          </div>
        </div>
      </div>
    </x-slot>
    <x-slot name="footer">
      <div class="flex justify-end space-x-4">
        <x-hub::button type="button" theme="gray" wire:click="$set('showUploader', false)">{{ __('adminhub::global.cancel') }}</x-hub::button>
        <x-hub::button
          type="button"
          :disabled="!count($selected)"
          wire:click.prevent="process"
        >
        Select files
        </x-hub::button>
      </div>
    </x-slot>

  </x-hub::modal.dialog>
</div>
