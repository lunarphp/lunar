<div>
    <div class="fixed bottom-0 left-0 right-0 z-40 p-6 border-t border-gray-100 lg:left-auto bg-white/75"
         :class="{
                 'lg:w-[calc(100vw_-_16rem)]': showExpandedMenu,
                 'lg:w-[calc(100vw_-_5rem)]': !showExpandedMenu
             }">
        <form wire:submit.prevent="{{ $submitAction }}">
            <div class="flex justify-end">
                <x-hub::button type="submit">
                    {{ __('adminhub::global.save') }}
                </x-hub::button>
            </div>
        </form>
    </div>

    <div class="mt-8 lg:gap-8 lg:flex lg:items-start">
        <div class="space-y-6 lg:flex-1">
            <div class="space-y-4">
                <div class="shadow sm:rounded-md">
                    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
                        @foreach($schema as $field)
                            @if(class_basename($field) === 'ImageManager')
                                @include('adminhub::partials.image-manager', [
                                    'existing' => $model->images,
                                    'wireModel' => 'imageUploadQueue',
                                    'filetypes' => ['image/*'],
                                ])
                            @elseif(class_basename($field) === 'UrlManager')
                                @include('adminhub::partials.urls')
                            @else
                                @include('adminhub::partials.form-field')
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- @todo Extract this into a livewire component and pass in the field to validate against -->
            @if ($model->id && !$model->getOriginal('default') && !$model->wasRecentlyCreated)
                <div class="bg-white border border-red-300 rounded shadow">
                    <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
                        {{ __('adminhub::inputs.danger_zone.title') }}
                    </header>

                    <div class="p-6 text-sm">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 md:col-span-6">
                                <strong>{{ __('adminhub::inputs.danger_zone.label', ['model' => class_basename($model)]) }}</strong>
                                <p class="text-xs text-gray-600">
                                    {{ __('adminhub::inputs.danger_zone.instructions', [
                                      'model' => class_basename($model),
                                      'attribute' => '"name"',
                                    ]) }}
                                </p>
                            </div>

                            <div class="col-span-9 lg:col-span-4">
                                <x-hub::input.text wire:model="deleteConfirm" />
                            </div>

                            <div class="col-span-3 text-right lg:col-span-2">
                                <x-hub::button :disabled="!$this->canDelete"
                                               wire:click="delete"
                                               type="button">{{ __('adminhub::global.delete') }}</x-hub::button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
