<div class="flex-col space-y-4">
  <div class="overflow-hidden shadow sm:rounded-md">
    <div class="flex-col px-4 py-5 space-y-4 bg-white sm:p-6">
      <x-hub::input.group :label="__('adminhub::inputs.name')" for="name" :error="$errors->first('channel.name')">
        <x-hub::input.text wire:model="channel.name" name="name" id="name" :error="$errors->first('channel.name')" />
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.handle')" for="handle" :error="$errors->first('channel.handle')">
        <x-hub::input.text wire:model.debounce.350ms="channel.handle" name="handle" id="handle" :error="$errors->first('channel.handle')" />
      </x-hub::input.group>

      <x-hub::input.group :label="__('adminhub::inputs.url')" for="url" :error="$errors->first('channel.url')">
        <x-hub::input.text wire:model="channel.url" name="url" id="url" :error="$errors->first('channel.url')" />
      </x-hub::input.group>

      <x-hub::input.group
        :label="__('adminhub::inputs.default.label')"
        for="handle"
        :instructions="__('adminhub::inputs.default.instructions', ['model' => 'channel'])"
      >
        <x-hub::input.toggle wire:model="channel.default" :disabled="$channel->id && $channel->getOriginal('default')" value="1" />
      </x-hub::input.group>
    </div>
    <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
      <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        {{__('adminhub::global.save') }}
      </button>
    </div>
  </div>
  @if($channel->id && !$channel->getOriginal('default') && !$channel->wasRecentlyCreated)
    <div class="bg-white border border-red-300 rounded shadow">
      <header class="px-6 py-4 text-red-700 bg-white border-b border-red-300 rounded-t">
        {{__('adminhub::danger_zone.title') }}
      </header>
      <div class="p-6 text-sm">
        <div class="grid grid-cols-12 gap-4">
          <div class="col-span-12 md:col-span-6">
            <strong>{{__('adminhub::partials.forms.channel.delete_channel') }}</strong>
            <p class="text-xs text-gray-600">{{__('adminhub::partials.forms.channel.channel_name_delete') }}</p>
          </div>
          <div class="col-span-9 lg:col-span-4">
            <x-hub::input.text wire:model="deleteConfirm" />
          </div>
          <div class="col-span-3 text-right lg:col-span-2">
            <x-hub::button :disabled="!$this->canDelete" wire:click="delete" type="button">{{__('adminhub::global.delete') }}</x-hub::button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
