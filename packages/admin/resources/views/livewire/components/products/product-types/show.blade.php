<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold md:text-xl">
            {{ __('adminhub::catalogue.product-types.show.title') }}
        </h1>

        @if ($this->canDelete)
            <x-hub::button theme="danger"
                           type="button"
                           wire:click="$set('deleteDialogVisible', true)">
                {{ __('adminhub::catalogue.product-types.show.delete.btn_text') }}
            </x-hub::button>
        @endif

        @if ($this->isTheOnlyProductType)
            <div class="text-sm text-gray-500">
                {{ __('adminhub::catalogue.product-types.show.delete.minimum_required') }}
            </div>
        @endif
    </div>

    @include('adminhub::livewire.components.products.product-types.partials.dialogs')

    @if ($this->showGroupCreate)
        @livewire('hub.components.settings.attributes.attribute-edit')
    @endif

    @include('adminhub::partials.forms.product-type')

    <div class="fixed bottom-0 left-0 right-0 z-40 p-6 border-t border-gray-100 lg:left-auto bg-white/75"
         :class="{
             'lg:w-[calc(100vw_-_16rem)]': showExpandedMenu,
             'lg:w-[calc(100vw_-_5rem)]': !showExpandedMenu
         }">
        <div class="flex justify-end">
            <form action="#"
                  method="POST"
                  wire:submit.prevent="update">
                <x-hub::button type="submit">
                    {{ __('adminhub::catalogue.product-types.show.btn_text') }}
                </x-hub::button>
            </form>
        </div>
    </div>
</div>
