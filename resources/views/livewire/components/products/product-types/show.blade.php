<div class="px-4 pb-24 space-y-6 md:px-12">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold md:text-xl">{{ __('adminhub::catalogue.product-types.show.title') }}</h1>
    <x-hub::button theme="danger" type="button" wire:click="$set('deleteDialogVisible', true)">
      {{ __('adminhub::catalogue.product-types.show.delete.btn_text') }}
    </x-hub::button>
  </div>

  <x-hub::modal.dialog wire:model="deleteDialogVisible">
    <x-slot name="title">
      {{ __('adminhub::catalogue.product-types.show.delete.confirm_text') }}
    </x-slot>

    <x-slot name="content">
      @if($this->canDelete)
        {{ __('adminhub::catalogue.product-types.show.delete.message') }}
      @else
        {{ __('adminhub::catalogue.product-types.show.delete.disabled_message') }}
      @endif
    </x-slot>

    <x-slot name="footer">
      <div class="flex items-center justify-end space-x-4">
        <x-hub::button theme="gray" type="button" wire:click="$set('deleteDialogVisible', false)">
          {{ __('adminhub::global.cancel') }}
        </x-hub::button>
        <x-hub::button wire:click="delete" :disabled="!$this->canDelete">
          {{ __('adminhub::catalogue.product-types.show.delete.confirm_text') }}
        </x-hub::button>
      </div>
    </x-slot>
  </x-hub::modal.dialog>
  @include('adminhub::partials.forms.product-type')
  <div class="fixed bottom-0 left-0 right-0 z-50 p-6 mr-0 text-right bg-white bg-opacity-75 border-t md:left-64">
    <div class="flex justify-end w-full space-x-6">
      <form action="#" method="POST" wire:submit.prevent="update">
        <x-hub::button type="submit">{{ __('adminhub::catalogue.product-types.show.btn_text') }}</x-hub::button>
      </form>
    </div>
  </div>
</div>
