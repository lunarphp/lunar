<div class="overflow-hidden shadow sm:rounded-md">
  <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
    <div class="grid grid-cols-2 gap-4">
      <x-hub::input.group label="{{ __('adminhub::inputs.firstname') }}" for="firstname" :error="$errors->first('staff.firstname')">
        <x-hub::input.text wire:model="staff.firstname" name="firstname" id="firstname" :error="$errors->first('staff.firstname')" />
      </x-hub::input.group>
      <x-hub::input.group label="{{ __('adminhub::inputs.lastname') }}" for="lastname" :error="$errors->first('staff.lastname')">
        <x-hub::input.text wire:model="staff.lastname" name="lastname" id="lastname" :error="$errors->first('staff.lastname')" />
      </x-hub::input.group>
    </div>

    <x-hub::input.group label="{{ __('adminhub::inputs.email') }}" for="email" :error="$errors->first('staff.email')">
      <x-hub::input.text wire:model="staff.email" type="email" name="email" id="email" :error="$errors->first('staff.email')" />
    </x-hub::input.group>

    <div class="grid grid-cols-2 gap-4">
      <x-hub::input.group label="{{ __('adminhub::inputs.new_password') }}" for="password" :error="$errors->first('password')">
        <x-hub::input.text wire:model="password" type="password" name="password" id="password" :error="$errors->first('password')" />
      </x-hub::input.group>
      <x-hub::input.group label="{{ __('adminhub::inputs.new_password_confirmation') }}" for="passwordConfirmation" :error="$errors->first('password_confirmation')">
        <x-hub::input.text wire:model="password_confirmation" type="password" name="password_confirmation" id="passwordConfirmation" :error="$errors->first('passwordConfirmation')" />
      </x-hub::input.group>
    </div>
  </div>
</div>
@include('adminhub::partials.forms.staff._permissions')
<div class="px-4 py-3 text-right rounded shadow bg-gray-50 sm:px-6">
  <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    {{ __(
      $staff->id ? 'adminhub::settings.staff.form.update_btn' : 'adminhub::settings.staff.form.create_btn'
    ) }}
  </button>
</div>

  @if($staff->id)
    <div class="bg-white border border-red-300 rounded shadow">
      <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
        {{ __('adminhub::inputs.danger_zone.title') }}
      </header>
      <div class="p-6 space-y-4 text-sm">
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-12 md:col-span-6">
            <strong>{{ __('adminhub::settings.staff.form.danger_zone.label') }}</strong>
            <p class="text-xs text-gray-600">{{ __('adminhub::settings.staff.form.danger_zone.instructions') }}</p>
          </div>
          <div class="col-span-9 lg:col-span-4">
            <x-hub::input.text type="email" wire:model="deleteConfirm" />
          </div>
          <div class="col-span-3 text-right lg:col-span-2">
            <x-hub::button theme="danger" :disabled="!$this->canDelete" wire:click="delete" type="button">Delete</x-hub::button>
          </div>
        </div>
        @if($this->ownAccount)
        <x-hub::alert level="danger">
          {{ __('adminhub::settings.staff.form.danger_zone.own_account') }}
        </x-hub:alert>
        @endif
      </div>
    </div>
  @endif
