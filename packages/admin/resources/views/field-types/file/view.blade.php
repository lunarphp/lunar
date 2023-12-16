<div>
    @livewire('hub.components.fieldtypes.file', [
    'maxFiles' => $field['configuration']['max_files'] ?? 1,
    'signature' => $field['signature'],
    'selected' => $field['data'] ?? []
    ], key($field['signature']))
</div>
