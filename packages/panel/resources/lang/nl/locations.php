<?php

return [
    'title' => 'Locations',
    'description' => 'Places that hold stock and dispatch fulfilments.',
    'create_location' => 'Create location',
    'create_description' => 'Add a place that holds stock and dispatches fulfilments.',
    'default_badge' => 'Default',
    'empty_title' => 'No locations',

    'column_name' => 'Name',
    'column_handle' => 'Handle',
    'column_stocked' => 'Stocked variants',

    'field_name' => 'Name',
    'name_placeholder' => 'e.g. Main warehouse',
    'field_handle' => 'Handle',
    'handle_hint' => 'lower-case-and-dashes',
    'handle_placeholder' => 'auto-generated',
    'default_location' => 'Default location',
    'default_location_hint' => 'Used when a fulfilment does not pick a location explicitly.',
    'default_locked_hint' => 'This is the default location. To change it, make another location the default.',
    'default_unset_blocked' => 'The default location cannot be unset. Make another location the default instead.',

    'section_details' => 'Details',
    'section_state' => 'State',
    'edit_title' => 'Edit location — {name}',

    'confirm_delete' => 'Are you sure you want to delete this location?',
    'confirm_delete_title' => 'Delete location?',
    'confirm_delete_body' => '"{name}" will be permanently removed.',
    'delete_blocked' => 'Cannot delete a location with fulfilments or stock history.',
    'delete_blocked_default' => 'The default location cannot be deleted. Make another location the default first.',

    'flash_created' => 'Location created.',
    'flash_updated' => 'Location updated.',
    'flash_deleted' => 'Location deleted.',
];
