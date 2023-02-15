<div class="space-y-6">
    @include('adminhub::partials.forms.product-type')
    <x-hub::layout.bottom-panel>
        <div class="flex justify-end">
            <form action="#"
                  method="POST"
                  wire:submit.prevent="create">
                <x-hub::button type="submit">
                    {{ __('adminhub::catalogue.product-types.create.btn_text') }}
                </x-hub::button>
            </form </div>
        </div>
    </x-hub::layout.bottom-panel>
