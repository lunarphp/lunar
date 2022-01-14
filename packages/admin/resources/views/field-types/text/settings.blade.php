<x-hub::input.group
  label="Field Type"
  for="fieldType"
  :error="$errors->first('attribute.configuration.type')"
>
  <x-hub::input.select :disabled="$attribute->system" wire:model="attribute.configuration.type" id="fieldType" :error="$errors->first('attribute.configuration.type')">
    <option value>Select a field type</option>
    <option value="text">Text</option>
    <option value="richtext">Richtext</option>
  </x-hub::input.select>
</x-hub::input.group>