<div>
  @if(($field['configuration']['richtext'] ?? false))
    <x-hub::input.richtext
      id="{{ $field['id'] }}"
      wire:model.defer="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}"
      :initial-value="isset($language) ? ($field['data'][$language] ?? null) : $field['data']"
    />
  @else
    <x-hub::input.text wire:model="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}" />
  @endif
</div>
