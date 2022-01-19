<div>
  <x-hub::input.select wire:model="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}">
    <option value readonly>
      {{ __('adminhub::fieldtypes.dropdown.empty_selection') }}
    </option>
    @foreach($field['configuration']['lookups'] as $lookup)
      <option value="{{ $lookup['value'] ?: $lookup['label'] }}">{{ $lookup['label'] }}</option>
    @endforeach
  </x-hub::input.select>
</div>