<div class="space-y-4">
<x-hub::input.group
  :label="__('adminhub::inputs.country.label')"
  for="country"
  :error="$errors->first('country')"
>
  <x-hub::input.select wire:model="country" id="country">
    @foreach($this->allCountries as $country)
      <option value="{{ $country->id }}">{{ $country->name }}</option>
    @endforeach
  </x-hub::input.select>
</x-hub::input.group>

<x-hub::input.group
  :label="__('adminhub::inputs.postcodes.label')"
  for="postcodes"
  :instructions="__('adminhub::inputs.postcodes.instructions')"
  :error="$errors->first('postcodes')"
>
  <x-hub::input.textarea wire:model.defer="postcodes" rows="15" />
</x-hub::input.group>
</div>
