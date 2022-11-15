<div class="bg-white shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 sm:p-6">
        <header class="flex items-center justify-between">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                {{ __('adminhub::partials.image-manager.heading') }}
            </h3>

            @if(!empty($chooseFrom))
                <div>
                    <x-hub::button theme="gray" type="button" wire:click="$set('showImageSelectModal', true)">
                        {{ __('adminhub::partials.image-manager.select_images_btn') }}
                    </x-hub::button>

                    <x-hub::modal.dialog wire:model="showImageSelectModal">
                        <x-slot name="title">
                            {{ __('adminhub::partials.image-manager.select_images') }}
                        </x-slot>
                        <x-slot name="content">
                          <div class="grid grid-cols-4 gap-4 overflow-y-auto max-h-96">
                            @forelse($chooseFrom as $productImage)
                              <label
                                @class([
                                    'cursor-pointer' => !in_array($productImage->id, $this->currentImageIds),
                                    'opacity-50 cursor-not-allowed' => in_array($productImage->id, $this->currentImageIds)
                                ]) wire:key="product_image_{{ $productImage->id }}">
                                <input
                                    wire:model="selectedImages"
                                    name="selectedImages"
                                    value="{{ $productImage->id }}"
                                    class="sr-only peer"
                                    type="checkbox"
                                    @if(in_array($productImage->id, $this->currentImageIds)) disabled @endif
                                >
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
                              :disabled="!count($selectedImages)"
                              wire:click.prevent="selectImages"
                            >
                                {{ __('adminhub::partials.image-manager.select_images_btn') }}
                            </x-hub::button>
                          </div>
                        </x-slot>

                      </x-hub::modal.dialog>
                </div>


            @endif
        </header>

        <div>
            <x-hub::input.fileupload wire:model="{{ $wireModel }}"
                                     :filetypes="$filetypes"
                                     multiple />
        </div>
        @if ($errors->has($wireModel . '*'))
            <x-hub::alert level="danger">{{ __('adminhub::partials.image-manager.generic_upload_error') }}
            </x-hub::alert>
        @endif

        <div>
            <div wire:sort
                 sort.options='{group: "images", method: "sort"}'
                 class="relative mt-4 space-y-2">
                @foreach ($this->images as $key => $image)
                    <div class="flex items-center justify-between p-4 bg-white border rounded-md shadow-sm"
                         sort.item="images"
                         sort.id="{{ $key }}"
                         wire:key="image_{{ $key }}">
                        <div class="flex items-center w-full space-x-6">
                            @if (count($images) > 1)
                                <div class="cursor-move"
                                     sort.handle>
                                    <x-hub::icon ref="dots-vertical"
                                                 style="solid"
                                                 class="text-gray-400 cursor-grab" />
                                </div>
                            @endif

                            <div>
                                <button type="button"
                                        wire:click="$set('images.{{ $key }}.preview', true)">
                                    <img src="{{ $image['thumbnail'] }}"
                                         class="w-8 overflow-hidden rounded-md" />
                                </button>
                                <x-hub::modal wire:model="images.{{ $key }}.preview">
                                    <img src="{{ $image['original'] }}">
                                </x-hub::modal>
                            </div>

                            <div class="w-full">
                                <x-hub::input.text wire:model="images.{{ $key }}.caption"
                                                   placeholder="Enter Alt. text" />
                            </div>

                            <div class="flex items-center ml-4 space-x-4">
                                <x-hub::tooltip text="Make primary">
                                    <x-hub::input.toggle :disabled="$image['primary']"
                                       wire:model="images.{{ $key }}.primary"
                                       :on="$image['primary']"
                                       wire:click.prevent="setPrimary('{{ $key }}')" />
                                </x-hub::tooltip>

                                @if (!empty($image['id']))
                                    <x-hub::tooltip :text="__('adminhub::partials.image-manager.remake_transforms')">
                                        <button wire:click.prevent="regenerateConversions('{{ $image['id'] }}')"
                                                href="{{ $image['original'] }}"
                                                type="button">
                                            <x-hub::icon ref="refresh"
                                                         style="solid"
                                                         class="text-gray-400 hover:text-indigo-500 hover:underline" />
                                        </button>
                                    </x-hub::tooltip>
                                @endif

                                <button type="button"
                                        wire:click.prevent="removeImage('{{ $key }}')"
                                        class="text-gray-400 hover:text-red-500 "
                                        @if($image['primary']) disabled @endif>
                                    <x-hub::icon ref="trash"
                                                 style="solid" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
