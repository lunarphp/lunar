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

    <x-hub::modal.dialog form="assignGroup" wire:model="showGroupAssign">
        <x-slot name="title">
            {{ __('Assign Product Option') }}
        </x-slot>

        <x-slot name="content">
            <x-hub::input.group :label="__('Options')" for="option" required :error="$errors->first('options')">
                <x-hub::input.select wire:model="selectedGroupId">
                    @foreach ($this->availableGroupOptions as $option)
                        <option value="{{ $option['id'] }}" @disabled($option['disabled'])>{{ $option['name'] }}</option>
                    @endforeach
                </x-hub::input.select>
            </x-hub::input.group>
        </x-slot>

        <x-slot name="footer">
            <x-hub::button type="button" wire:click.prevent="$set('showGroupAssign', false)" theme="gray">
                {{ __('adminhub::global.cancel') }}
            </x-hub::button>
            <x-hub::button type="submit" theme="green">
                {{ __('Assign') }}
            </x-hub::button>
        </x-slot>
    </x-hub::modal.dialog>

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
