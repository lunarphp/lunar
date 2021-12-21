<div>
  <x-hub::button theme="gray" wire:click.prevent="$set('showCreateModal', true)">
    {{ __('adminhub::catalogue.collections.sidemenu.create_btn') }}
  </x-hub::button>
  <x-hub::modal.dialog form="createCollectionGroup" wire:model="showCreateModal">
    <x-slot name="title">{{ __('adminhub::catalogue.collections.sidemenu.modal.title') }}</x-slot>
    <x-slot name="content">
      <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" required :error="$errors->first('name')">
        <x-hub::input.text wire:model="name" :error="$errors->first('name')" />
      </x-hub::input.group>
    </x-slot>
    <x-slot name="footer">
      <x-hub::button type="button" wire:click.prevent="$set('showCreateModal', false)" theme="gray">{{ __('adminhub::global.cancel') }}</x-hub::button>
      <x-hub::button type="submit">{{ __('adminhub::catalogue.collections.sidemenu.modal.btn') }}</x-hub::button>
    </x-slot>
  </x-hub::modal.dialog>
  <header class="my-4">
    <h3 class="font-medium">{{ __('adminhub::catalogue.collections.sidemenu.menu_title') }}</h3>
  </header>
  <nav class="mt-4 space-y-1">
    @forelse($this->collectionGroups as $group)
      <a
        href="{{ route('hub.collection-groups.show', $group->id) }}"
        class="flex items-center px-3 py-2 text-sm font-medium @if($currentGroup && $currentGroup->id == $group->id) bg-gray-50 text-indigo-700 hover:text-indigo-700 hover:bg-white @else text-gray-900 hover:text-gray-900 hover:bg-gray-50 @endif rounded-md  group"
        aria-current="page"
      >
        {{ $group->name }}
      </a>
    @empty
    @endforelse
  </nav>
</div>
