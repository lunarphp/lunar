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

@if ($staff->id)
    <div
        @class([
            'bg-white border rounded shadow',
            'border-red-300' => !$staff->deleted_at,
            'border-gray-300' => $staff->deleted_at,
        ])
    >
        <header
            @class([
                'px-6 py-4 bg-white border-b rounded-t',
                'border-red-300 text-red-700' => !$staff->deleted_at,
                'border-gray-300 text-gray-700' => $staff->deleted_at,
            ])
        >
            @if($staff->deleted_at)
                {{ __('adminhub::inputs.restore_zone.title') }}
            @else
                {{ __('adminhub::inputs.danger_zone.title') }}
            @endif

        </header>



        <div class="p-6 space-y-4 text-sm">

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-8">
                    <strong>
                        @if($staff->deleted_at)
                            {{ __('adminhub::inputs.restore_zone.label', ['model' => __('adminhub::types.staff')]) }}
                        @else
                            {{ __('adminhub::inputs.danger_zone.label', ['model' => __('adminhub::types.staff'])}}
                        @endif
                    </strong>

                    <p class="text-xs text-gray-600">
                        @if($staff->deleted_at)
                            {{ __('adminhub::settings.staff.form.danger_zone.restore_strapline') }}
                        @else
                            {{ __('adminhub::settings.staff.form.danger_zone.delete_strapline') }}
                        @endif

                    </p>
                </div>

                <div class="col-span-6 text-right lg:col-span-4">
                    @if($staff->deleted_at)
                        <x-hub::button :disabled="false"
                                       wire:click="$set('showRestoreConfirm', true)"
                                       type="button"
                                       theme="green">
                            {{ __('adminhub::global.restore') }}
                        </x-hub::button>
                    @else
                        <x-hub::button :disabled="false"
                                       wire:click="$set('showDeleteConfirm', true)"
                                       type="button"
                                       theme="danger">
                            {{ __('adminhub::global.delete') }}
                        </x-hub::button>
                    @endif
                </div>
            </div>

            @if($this->ownAccount)
            <x-hub::alert level="danger" class="rounded-none">
              {{ __('adminhub::settings.staff.form.danger_zone.own_account') }}
            </x-hub:alert>
            @endif
        </div>
    </div>

    <x-hub::modal.dialog wire:model="showRestoreConfirm">
        <x-slot name="title">
            {{ __('adminhub::settings.staff.show.restore_title') }}
        </x-slot>

        <x-slot name="content">
            {{ __('adminhub::settings.staff.form.danger_zone.restore_strapline') }}
        </x-slot>

        <x-slot name="footer">
            <div class="flex items-center justify-end space-x-4">
                <x-hub::button theme="gray"
                               type="button"
                               wire:click.prevent="$set('showRestoreConfirm', false)">
                    {{ __('adminhub::global.cancel') }}
                </x-hub::button>

                <x-hub::button wire:click="restore"
                               type="button"
                               theme="green">
                    {{ __('adminhub::catalogue.products.show.restore_btn') }}
                </x-hub::button>
            </div>
        </x-slot>
    </x-hub::modal.dialog>

    <x-hub::modal.dialog wire:model="showDeleteConfirm">
        <x-slot name="title">
            {{ __('adminhub::settings.staff.show.delete_title') }}
        </x-slot>

        <x-slot name="content">
            {{ __('adminhub::settings.staff.form.danger_zone.delete_strapline') }}
        </x-slot>

        <x-slot name="footer">
            <div class="flex items-center justify-end space-x-4">
                <x-hub::button theme="gray"
                               type="button"
                               wire:click.prevent="$set('showDeleteConfirm', false)">
                    {{ __('adminhub::global.cancel') }}
                </x-hub::button>

                <x-hub::button wire:click="delete"
                               theme="danger">
                    {{ __('adminhub::catalogue.products.show.delete_btn') }}
                </x-hub::button>
            </div>
        </x-slot>
    </x-hub::modal.dialog>
@endif
