<div class="max-w-2xl mx-auto">
  <div class="p-4 bg-white rounded shadow">

    <form method="POST" wire:submit.prevent="save" class="space-y-4 ">
      <div class="flex items-center">
        <div><img class="inline-block w-10 h-10 rounded-full" src="{{ $staff->gravatar }}" alt=""></div>
        <div class="grow ml-4">
          <x-hub::alert>
            {{ __('adminhub::account.avatar_notice') }}
          </x-hub::alert>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <x-hub::input.group for="firstname" :label="__('adminhub::global.firstname')" :error="$errors->first('staff.firstname')">
          <x-hub::input.text id="firstname" wire:model="staff.firstname" :error="$errors->first('staff.firstname')" />
        </x-hub::input.group>

        <x-hub::input.group for="lastname" :label="__('adminhub::global.lastname')" :error="$errors->first('staff.lastname')">
          <x-hub::input.text id="lastname" wire:model="staff.lastname" :error="$errors->first('staff.lastname')" />
        </x-hub::input.group>
      </div>

      <x-hub::input.group for="email" :label="__('adminhub::global.email')" :error="$errors->first('staff.email')">
        <x-hub::input.text id="email" wire:model="staff.email" :error="$errors->first('staff.email')" type="email" />
      </x-hub::input.group>



      <h3>{{ __('adminhub::account.update_password') }}</h3>
      <div class="grid grid-cols-2 gap-4">
        <x-hub::input.group for="currentPassword" :label="__('adminhub::global.current_password')" :error="$errors->first('currentPassword')">
          <x-hub::input.password id="currentPassword" wire:model="currentPassword" :error="$errors->first('currentPassword')" />
        </x-hub::input.group>

        <x-hub::input.group for="lastname" :label="__('adminhub::global.new_password')" :error="$errors->first('password')">
          <x-hub::input.password id="new_password" wire:model="password" :error="$errors->first('password')" />
        </x-hub::input.group>
      </div>

      <div class="pt-4 mt-4 text-right border-t">
        <x-hub::button>{{ __('adminhub::account.save_btn') }}</x-hub::button>
      </div>
    </form>
  </div>
</div>