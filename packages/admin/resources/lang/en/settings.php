<?php

return [
    /**
     * Channels.
     */
    'channels.index.create_btn'            => 'Create Channel',
    'channels.index.table_row_action_text' => 'Edit channel',
    /**
     * Settings layout.
     */
    'layout.menu_btn' => 'Settings menu',
    /**
     * Staff listing page.
     */
    'staff.index.title'                 => 'Staff',
    'staff.index.search_placeholder'    => 'Search staff',
    'staff.index.active_filter'         => 'Show Inactive',
    'staff.index.create_btn'            => 'Add Staff',
    'staff.index.table_row_action_text' => 'Edit',
    /**
     * Staff show page.
     */
    'staff.show.delete_btn' => 'Deactivate account',
    /**
     * Staff form.
     */
    'staff.form.create_btn'               => 'Create staff member',
    'staff.form.update_btn'               => 'Update staff member',
    'staff.form.permissions_heading'      => 'Permissions',
    'staff.form.permissions_description'  => 'Manage a staff members individual permissions.',
    'staff.form.admin_message'            => 'An admin user has access to all permissions.',
    'staff.form.danger_zone.label'        => 'Remove staff member',
    'staff.form.danger_zone.instructions' => 'Enter the staff members email address to confirm removal.',
    'staff.form.danger_zone.own_account'  => 'Removing your own account will instantly log you out.',
    /**
     * Addons.
     */
    'addons.index.table_row_action_text' => 'View',
    /*
     * Languages listing page.
     */
    'languages.index.title'                 => 'Languages',
    'languages.index.create_btn'            => 'Create Language',
    'languages.index.table_row_action_text' => 'Edit',
    /**
     * Languages create page.
     */
    'languages.create.title' => 'Create Language',
    /**
     * Languages show page.
     */
    'languages.show.title' => 'Edit Language',
    /**
     * Language form.
     */
    'languages.form.create_btn'           => 'Create Language',
    'languages.form.update_btn'           => 'Update Language',
    'languages.form.default_instructions' => 'Set whether this language is the default, this will override the current default.',
    /**
     * Currencies table.
     */
    'currencies.index.title'                 => 'Currencies',
    'currencies.index.table_row_action_text' => 'Edit',
    'currencies.index.no_results'            => 'You currently have no currencies in the system.',
    /**
     * Currency show page.
     */
    'currencies.show.title' => 'Edit Currency',
    /**
     * Currency create page.
     */
    'currencies.create.title' => 'Edit Currency',
    /**
     * Currency form.
     */
    'currencies.form.update_btn'       => 'Update Currency',
    'currencies.form.create_btn'       => 'Create Currency',
    'currencies.form.notify.created'   => 'Currency created',
    'currencies.form.format_help_text' => [
        'This allows you to specify the format that price fields should use for this currency.',
        'When displaying, GetCandy will swap out <code>{value}</code> for the formatted price. E.g. <code>£{value}</code>.',
        'You must always include <code>{value}</code> for this to work properly.',
    ],
    /*
     * Addons.
     */
    'addons.index.table_row_action_text' => 'View',
    /**
     * Attributes.
     */
    'attributes.index.title'         => 'Attributes',
    'attributes.show.title'          => 'Editing :type attributes',
    'attributes.show.locked'         => 'This attribute is required by the system and therefore has been locked for editing.',
    'attributes.create.title'        => 'Create Attribute',
    'attributes.form.update_btn'     => 'Update Attribute',
    'attributes.form.create_btn'     => 'Create Attribute',
    'attributes.form.notify.created' => 'Attribute created',
    /**
     * Tags.
     */
    'tags.index.title'                 => 'Tags',
    'tags.index.table_row_action_text' => 'Edit',
];
