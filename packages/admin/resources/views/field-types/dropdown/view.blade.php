<div>
  <x-hub::input.select wire:model="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}">
    @foreach($field['configuration']['lookups'] as $lookup)
      <option value="{{ $lookup['value'] ?: $lookup['label'] }}">{{ $lookup['label'] }}</option>
    @endforeach
  </x-hub::input.select>
</div>