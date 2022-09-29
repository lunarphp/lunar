<div class="space-y-4">
    <header>
        <div class="flex items-center gap-4">
            <a href="{{ route('hub.brands.index') }}"
               class="text-gray-600 rounded bg-gray-50 hover:bg-indigo-500 hover:text-white"
               title="{{ __('adminhub::catalogue.brands.show.back_link_title') }}">
                <x-hub::icon ref="chevron-left"
                             style="solid"
                             class="w-8 h-8" />
            </a>
            <h1 class="text-xl font-bold md:text-xl">
                {{ $brand->name }}
            </h1>
        </div>
    </header>

    <div class="fixed bottom-0 left-0 right-0 z-40 p-6 border-t border-gray-100 lg:left-auto bg-white/75"
         :class="{
             'lg:w-[calc(100vw_-_16rem)]': showExpandedMenu,
             'lg:w-[calc(100vw_-_5rem)]': !showExpandedMenu
         }">
        <form wire:submit.prevent="update">
            <div class="flex justify-end">
                <x-hub::button type="submit">
                    {{ __('adminhub::global.save') }}
                </x-hub::button>
            </div>
        </form>
    </div>

    <div class="mt-8 lg:gap-8 lg:flex lg:items-start">
        <div class="space-y-6 lg:flex-1">
            <div>
                @include('adminhub::partials.forms.brand')
            </div>

            <div id="images">
                @include('adminhub::partials.image-manager', [
                    'existing' => $images,
                    'wireModel' => 'imageUploadQueue',
                    'filetypes' => ['image/*'],
                ])
            </div>

            <div id="urls">
                @include('adminhub::partials.urls')
            </div>
        </div>
    </div>

    @if ($brand->id && !$brand->getOriginal('default') && !$brand->wasRecentlyCreated)
        <div class="!mb-24 bg-white border border-red-300 rounded shadow">
            <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
                {{ __('adminhub::inputs.danger_zone.title') }}
            </header>

            <div class="p-6 text-sm">
                <div class="grid items-center grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6">
                        <strong>{{ __('adminhub::partials.forms.brand_delete_brand') }}</strong>

                        <p class="text-xs text-gray-600">
                            {{ __('adminhub::partials.forms.brand_name_delete') }}
                        </p>
                    </div>

                    @if ($this->productsCount > 0)
                        <div class="col-span-12 text-right text-red-600 md:col-span-6">
                            {{ __('adminhub::notifications.brands.delete_protected') }}
                        </div>
                    @else
                        <div class="col-span-9 lg:col-span-4">
                            <x-hub::input.text wire:model="deleteConfirm" />
                        </div>

                        <div class="col-span-3 text-right lg:col-span-2">
                            <x-hub::button :disabled="!$this->canDelete"
                                           theme="danger"
                                           wire:click="delete"
                                           type="button">
                                {{ __('adminhub::global.delete') }}
                            </x-hub::button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
