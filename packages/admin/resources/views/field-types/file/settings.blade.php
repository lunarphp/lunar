<div class="space-y-4">
  <x-hub::input.group
    label="Max files"
    for="max_files"
    :error="$errors->first('attribute.configuration.max_files')"
    :disabled="!!$attribute->system"
  >
   <x-hub::input.text wire:model.defer="attribute.configuration.max_files" id="path" />
  </x-hub::input.group>

</div>
