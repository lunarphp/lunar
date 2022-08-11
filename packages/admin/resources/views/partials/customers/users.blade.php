<div>
  <div class="space-y-4">
    {{-- <div class="flex justify-end w-full">
      <x-hub::button>Associate User</x-hub::button>
    </div> --}}
    {{ $this->users->links() }}

    @foreach($this->users as $user)
      <div wire:key="user_{{ $user->id }}" class="flex items-center justify-between p-4 bg-white rounded-md shadow">
        <div class="flex items-center">
          <x-hub::gravatar email="{{ $user->email }}" class="w-8 h-8 rounded-full" />
          <div class="flex ml-4 space-x-4 text-sm">
            <span class="block">{{ $user->name }}</span>
            <span class="block">{{ $user->email }}</span>
          </div>
        </div>
        <div>
          <x-hub::dropdown minimal>
            <x-slot name="options">
              <x-hub::dropdown.button wire:click="sendPasswordReset('{{ $user->id }}')">
                Send password reset
              </x-hub::dropdown.button>

              <x-hub::dropdown.button wire:click="$set('userIdToRemove', {{ $user->id }})">
                Remove User
              </x-hub::dropdown.button>

              @if(impersonate_link($user))
                <x-hub::dropdown.link href="{{ impersonate_link($user) }}" target="_blank" nofollow>
                  Impersonate
                </x-hub::dropdown.link>
              @endif
            </x-slot>
          </x-hub::dropdown>
        </div>
      </div>
    @endforeach
  </div>

  <x-hub::modal.dialog form="removeUser" wire:model="userIdToRemove">
    <x-slot name="title">
      Remove user
    </x-slot>
    <x-slot name="content">
      <x-hub::alert level="warning">
        Are you sure? This will not delete the user, only remove the association to this customer.
      </x-hub::alert>
    </x-slot>
    <x-slot name="footer">
      <x-hub::button type="button" wire:click.prevent="$set('userIdToRemove', null)" theme="gray">{{ __('adminhub::global.cancel') }}</x-hub::button>
      <x-hub::button type="submit">Confirm and remove</x-hub::button>
    </x-slot>
  </x-hub::modal.dialog>

  {{-- <x-hub::slideover title="Associate User" wire:model="showUserAssociate">
    <div>
      <x-hub::input.text wire:model="userSearchTerm" placeholder="Search via email address" />

      <div class="mt-4 space-y-4">
        @foreach($this->searchedUsers as $user)
          <div class="flex justify-between">
            <div class="flex items-center">
              <x-hub::gravatar email="{{ $user->email }}" class="w-8 h-8 rounded-full" />
              <div class="flex ml-4 space-x-4 text-sm">
                <span class="block">{{ $user->name }}</span>
                <span class="block">{{ $user->email }}</span>
              </div>
            </div>
            <div>
              @if(!$this->currentUserIds->contains($user->id))
                <x-hub::button size="xs">Associate</x-hub::button>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </x-hub::slideover> --}}
</div>
