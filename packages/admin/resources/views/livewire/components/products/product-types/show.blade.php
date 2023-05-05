<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold md:text-xl">
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

    <x-hub::modal.dialog wire:model="deleteDialogVisible">
        <x-slot name="title">
            {{ __('adminhub::catalogue.product-types.show.delete.confirm_text') }}
        </x-slot>

        <x-slot name="content">
            @if ($this->canDelete)
                {{ __('adminhub::catalogue.product-types.show.delete.message') }}
            @else
                {{ __('adminhub::catalogue.product-types.show.delete.disabled_message') }}
            @endif
        </x-slot>

        <x-slot name="footer">
            <div class="flex items-center justify-end space-x-4">
                <x-hub::button theme="gray"
                               type="button"
                               wire:click="$set('deleteDialogVisible', false)">
                    {{ __('adminhub::global.cancel') }}
                </x-hub::button>

                <x-hub::button wire:click="delete"
                               :disabled="!$this->canDelete">
                    {{ __('adminhub::catalogue.product-types.show.delete.confirm_text') }}
                </x-hub::button>
            </div>
        </x-slot>
    </x-hub::modal.dialog>

    @include('adminhub::partials.forms.product-type')

    <x-hub::layout.bottom-panel>
        <div class="flex justify-end">
            <form action="#"
                  method="POST"
                  wire:submit.prevent="update">
                <x-hub::button type="submit">
                    {{ __('adminhub::catalogue.product-types.show.btn_text') }}
                </x-hub::button>
            </form>
        </div>
    </x-hub::layout.bottom-panel>
</div>
