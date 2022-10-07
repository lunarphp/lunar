<div>
    @if ($field['configuration']['richtext'] ?? false)
        <x-hub::input.richtext id="{{ $field['id'] }}"
                               wire:model.defer="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}"
                               :initial-value="isset($language) ? $field['data'][$language] ?? null : $field['data']"
                               :options="json_decode($field['configuration']['options'] ?? '[]', true)" />
    @else
        <x-hub::input.text
                           wire:model.defer="{{ $field['signature'] }}{{ isset($language) ? '.' . $language : null }}" />
    @endif
</div>
