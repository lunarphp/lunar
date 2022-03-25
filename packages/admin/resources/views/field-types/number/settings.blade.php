<div>
  <div class="grid grid-cols-2 gap-4">
    <x-hub::input.group
      :label="__('adminhub::inputs.min.label')"
      for="min"
      :error="$errors->first('attribute.configuration.min')"
    >
      <x-hub::input.text
        type="number"
        id="min"
        wire:model="attribute.configuration.min"
        :error="$errors->first('attribute.configuration.type')"
      />
    </x-hub::input.group>

    <x-hub::input.group
      :label="__('adminhub::inputs.max.label')"
      for="max"
      :error="$errors->first('attribute.configuration.max')"
    >
      <x-hub::input.text
        type="number"
        id="max"
        wire:model="attribute.configuration.max"
        :error="$errors->first('attribute.configuration.max')"
      />
    </x-hub::input.group>
  </div>

</div>