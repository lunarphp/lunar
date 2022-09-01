<div class="bg-white shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 sm:p-6">
        <header>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                {{ __('adminhub::partials.image-manager.heading') }}
            </h3>
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
                @foreach ($this->images as $image)
                    <div class="flex items-center justify-between p-4 bg-white border rounded-md shadow-sm"
                         sort.item="images"
                         sort.id="{{ $image['sort_key'] }}"
                         wire:key="image_{{ $image['sort_key'] }}">
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
                                        wire:click="$set('images.{{ $loop->index }}.preview', true)">
                                    <img src="{{ $image['thumbnail'] }}"
                                         class="w-8 overflow-hidden rounded-md" />
                                </button>

                                @if($images[$loop->index]['preview'] )
                                    <x-hub::modal wire:model="images.{{ $loop->index }}.preview">
                                        <img src="{{ $image['original'] }}">
                                    </x-hub::modal>
                                @endif

                                @if($images[$loop->index]['edit'])
                                    <x-hub::modal wire:model="images.{{ $loop->index }}.edit" max-width="5xl">
                                        <div
                                            x-data="{
                                                filerobotImageEditor: null,

                                                init() {
                                                    const { TABS, TOOLS } = FilerobotImageEditor;

                                                    const config = {
                                                        source: '{{ $image['original'] }}',
                                                        Rotate: { angle: 45, componentType: 'slider' },
                                                        theme: {
                                                            typography: {
                                                              fontFamily: 'Nunito, Arial',
                                                            },
                                                        }
                                                    }

                                                    filerobotImageEditor = new FilerobotImageEditor($el, config);

                                                    filerobotImageEditor.render({
                                                        onClose: (closingReason) => {
                                                            @this.set('images.{{ $loop->index }}.edit', false)

                                                            filerobotImageEditor.terminate();
                                                        },
                                                        onBeforeSave: (imageFileInfo) => false,
                                                        onSave: (imageData, imageDesignState) => {
                                                            fetch(imageData.imageBase64)
                                                                .then(res => res.blob())
                                                                .then(blob => {
                                                                    const file = new File([blob], imageData.fullName,{ type: imageData.mimeType })

                                                                    @this.upload('images.{{ $loop->index }}.file', file)

                                                                    @this.set('images.{{ $loop->index }}.edit', false)
                                                                })
                                                        }
                                                    });
                                                }
                                            }"

                                        >
                                        </div>
                                    </x-hub::modal>
                                @endif
                            </div>

                            <div class="w-full">
                                <x-hub::input.text wire:model="images.{{ $loop->index }}.caption"
                                                   placeholder="Enter Alt. text" />
                            </div>

                            <div class="flex items-center ml-4 space-x-4">
                                <x-hub::tooltip text="Make primary">
                                    <x-hub::input.toggle :disabled="$image['primary']"
                                                         :on="$image['primary']"
                                                         wire:click.prevent="setPrimary('{{ $loop->index }}')" />
                                </x-hub::tooltip>

                                @if (!empty($image['id']))
                                    <x-hub::tooltip :text="__('adminhub::partials.image-manager.remake_transforms')">
                                        <button wire:click.prevent="regenerateConversions('{{ $image['id'] }}')"
                                                href="{{ $image['original'] }}"
                                                type="button">
                                            <x-hub::icon ref="refresh"
                                                         style="solid"
                                                         class="text-gray-400 hover:text-indigo-500" />
                                        </button>
                                    </x-hub::tooltip>
                                @endif

                                <button type="button"
                                    wire:click="$set('images.{{ $loop->index }}.edit', true)">
                                    <x-hub::icon ref="pencil"
                                                 style="solid"
                                                 class="text-gray-400 hover:text-indigo-500" />
                                </button>

                                <button type="button"
                                        wire:click.prevent="removeImage('{{ $image['sort_key'] }}')">
                                    <x-hub::icon ref="trash"
                                                 style="solid"
                                                 class="text-gray-400 hover:text-red-500" />
                                </button>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
