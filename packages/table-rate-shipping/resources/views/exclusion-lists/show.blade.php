<div class="flex-col px-8 space-y-4 md:px-12">
  <div class="space-y-4">
    <form action="#" wire:submit.prevent="save" class="shadow sm:rounded-md">
      @include('shipping::partials.forms.exclusion-list')
    </form>
  </div>

  <x-hub::modal.dialog form="removeList" wire:model="showRemoveModal">
    <x-slot name="title">
      Delete List
    </x-slot>
    <x-slot name="content">
      <x-hub::alert level="danger">
        Are you sure? This action cannot be undone.
      </x-hub::alert>
    </x-slot>
    <x-slot name="footer">
      <x-hub::button type="button" wire:click.prevent="$set('showRemoveModal', false)" theme="gray">
        {{ __('adminhub::global.cancel') }}
      </x-hub::button>
      <x-hub::button type="submit" theme="danger">Remove</x-hub::button>
    </x-slot>
  </x-hub::modal.dialog>
</div>
