<x-hub::input.group
  label="Country"
  for="country"
  :error="$errors->first('country')"
>
  <x-hub::input.select wire:model="country" id="country">
    @foreach($this->countries as $country)
      <option value="{{ $country->id }}">{{ $country->name }}</option>
    @endforeach
  </x-hub::input.select>
</x-hub::input.group>
<x-hub::input.group
  label="Postcodes"
  for="postcodes"
  instructions="List each postcode on a new line. Supports wildcards such as NW*"
  :error="$errors->first('postcodes')"
>
  <x-hub::input.textarea wire:model.defer="postcodes" rows="15" />
</x-hub::input.group>
