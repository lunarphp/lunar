<div class="space-y-6">
    @include('adminhub::partials.forms.product-type')
    <div class="fixed bottom-0 right-0 left-auto z-40 p-6 border-t bg-white/75"
         :class="{ 'w-[calc(100vw_-_12rem)]': showExpandedMenu, 'w-[calc(100vw_-_5rem)]': !showExpandedMenu }">
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
