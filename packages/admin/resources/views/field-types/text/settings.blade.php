<x-hub::input.group
  label="Richtext"
  for="richtext"
  :error="$errors->first('attribute.configuration.richtext')"
>
  <x-hub::input.toggle wire:model="attribute.configuration.richtext" id="fieldType" />
</x-hub::input.group>