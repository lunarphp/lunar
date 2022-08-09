<div class="space-y-6">
    @include('adminhub::partials.forms.product-type')
    <div class="fixed bottom-0 left-0 right-0 z-40 p-6 border-t border-gray-100 lg:left-auto bg-white/75"
         :class="{
             'lg:w-[calc(100vw_-_16rem)]': showExpandedMenu,
             'lg:w-[calc(100vw_-_5rem)]': !showExpandedMenu
         }">
        <div class="flex justify-end">
            <form action="#"
                  method="POST"
                  wire:submit.prevent="create">
                <x-hub::button type="submit">
                    {{ __('adminhub::catalogue.product-types.create.btn_text') }}
                </x-hub::button>
            </form </div>
    </div>
</div>
