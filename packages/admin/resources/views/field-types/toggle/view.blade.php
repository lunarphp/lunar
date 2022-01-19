<div>
  <x-hub::input.toggle
    wire:model.defer="{{ $field['signature'] }}"
    :on-value="$field['configuration']['on_value'] ?: true"
    :off-value="$field['configuration']['off_value'] ?: false"
  />
</div>