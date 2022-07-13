<?php

return [
    /**
     * Channels.
     */
    'channels.index.title'                 => 'Channels',
    'channels.index.create_btn'            => 'Create channel',
    'channels.index.table_row_action_text' => 'Edit channel',
    /**
     * Channels show page.
     */
    'channels.show.title' => 'Edit Channel',
    /**
     * Channels create page.
     */
    'channels.create.title' => 'Create Channel',
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
    'staff.index.table_row_action_text' => 'Edit staff',
    /**
     * Staff show page.
     */
    'staff.show.title' => 'Edit Staff',
    'staff.show.delete_btn' => 'Deactivate account',
    /**
     * Staff create page.
     */
    'staff.create.title' => 'Create Staff',
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
     * Addons listing page.
     */
    'addons.index.title' => 'Addons',
    'addons.index.table_row_action_text' => 'View',
    /**
     * Addons show page.
     */
    'addons.show.title' => 'Addon',
    /*
     * Languages listing page.
     */
    'languages.index.title'                 => 'Languages',
    'languages.index.create_btn'            => 'Create Language',
    'languages.index.table_row_action_text' => 'Edit language',
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
    'currencies.create.title' => 'Create Currency',
    'currencies.index.create_currency_btn' => 'Create Currency',
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
    'tags.show.title'                  => 'Edit Tag',
    'tags.index.title'                 => 'Tags',
    'tags.index.table_row_action_text' => 'Edit',
    'tags.form.update_btn'             => 'Update Tag',
    'tags.form.create_btn'             => 'Create Tag',
    'tags.form.notify.updated'         => 'Tag updated',
    /**
     * Activity log page.
     */
    'activity_log.index.title' => 'Activity Log',

    /**
     * Taxes
     */
    'taxes.tax-zones.index.title'             => 'Taxes',
    'taxes.tax-zones.index.table_row_action_text' => 'Manage',
];
