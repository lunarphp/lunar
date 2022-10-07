<x-hub::input.text type="number"
                   id="{{ $field['id'] }}"
                   min="{{ $field['configuration']['min'] }}"
                   max="{{ $field['configuration']['max'] }}"
                   wire:model.defer="{{ $field['signature'] }}" />
