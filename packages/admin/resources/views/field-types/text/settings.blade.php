<x-hub::input.group
  label="Richtext"
  for="richtext"
  :error="$errors->first('attribute.configuration.richtext')"
  :disabled="!!$attribute->system"
>
  <x-hub::input.toggle :disabled="!!$attribute->system" wire:model="attribute.configuration.richtext" id="fieldType" />
</x-hub::input.group>