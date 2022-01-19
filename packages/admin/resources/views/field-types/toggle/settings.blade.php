<div class="space-y-4">
  <x-hub::alert>
    {{ __('adminhub::fieldtypes.toggle.empty_notice') }}
  </x-hub::alert>

  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group for="onValue" :label="__('adminhub::fieldtypes.toggle.on_label')">
      <x-hub::input.text id="onValue" wire:model="attribute.configuration.on_value" />
    </x-hub::input.group>

    <x-hub::input.group for="offValue" :label="__('adminhub::fieldtypes.toggle.off_label')">
      <x-hub::input.text id="offValue" wire:model="attribute.configuration.off_value" />
    </x-hub::input.group>
  </div>
</div>