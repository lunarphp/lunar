<?php

return [
    /**
     * Channels.
     */
    'channels.index.title' => 'Channels',
    'channels.index.create_btn' => 'Create channel',
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
    'staff.index.title' => 'Staff',
    'staff.index.search_placeholder' => 'Search staff',
    'staff.index.active_filter' => 'Show Inactive',
    'staff.index.create_btn' => 'Add Staff',
    'staff.index.table_row_action_text' => 'Edit staff',
    /**
     * Staff show page.
     */
    'staff.show.title' => 'Edit Staff',
    'staff.show.delete_btn' => 'Deactivate account',
    'staff.show.delete_title' => 'Delete Staff',
    'staff.show.restore_title' => 'Restore Staff',
    /**
     * Staff create page.
     */
    'staff.create.title' => 'Create Staff',
    /**
     * Staff form.
     */
    'staff.form.create_btn' => 'Create staff member',
    'staff.form.update_btn' => 'Update staff member',
    'staff.form.permissions_heading' => 'Permissions',
    'staff.form.permissions_description' => 'Manage a staff members individual permissions.',
    'staff.form.admin_message' => 'An admin user has access to all permissions.',
    'staff.form.danger_zone.label' => 'Remove staff member',
    'staff.form.danger_zone.delete_strapline' => 'Removing a staff member will stop all access to the hub, you can restore it later.',
    'staff.form.danger_zone.restore_strapline' => 'Restore this staff members account so they can access the hub.',
    'staff.form.danger_zone.own_account' => 'Removing your own account will instantly log you out.',

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
    'languages.index.title' => 'Languages',
    'languages.index.create_btn' => 'Create Language',
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
    'languages.form.create_btn' => 'Create Language',
    'languages.form.update_btn' => 'Update Language',
    'languages.form.default_instructions' => 'Set whether this language is the default, this will override the current default.',
    /**
     * Currencies table.
     */
    'currencies.index.title' => 'Currencies',
    'currencies.index.table_row_action_text' => 'Edit',
    'currencies.index.no_results' => 'You currently have no currencies in the system.',
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
    'currencies.form.update_btn' => 'Update Currency',
    'currencies.form.create_btn' => 'Create Currency',
    'currencies.form.notify.created' => 'Currency created',
    'currencies.form.format_help_text' => [
        'This allows you to specify the format that price fields should use for this currency.',
        'When displaying, Lunar will swap out <code>{value}</code> for the formatted price. E.g. <code>£{value}</code>.',
        'You must always include <code>{value}</code> for this to work properly.',
    ],
    /**
     * Attributes.
     */
    'attributes.index.title' => 'Attributes',
    'attributes.show.title' => 'Editing :type attributes',
    'attributes.show.locked' => 'This attribute is required by the system and therefore has been locked for editing.',
    'attributes.create.title' => 'Create Attribute',
    'attributes.form.update_btn' => 'Update Attribute',
    'attributes.form.create_btn' => 'Create Attribute',
    'attributes.form.notify.created' => 'Attribute created',
    /**
     * Tags.
     */
    'tags.show.title' => 'Edit Tag',
    'tags.index.title' => 'Tags',
    'tags.index.table_row_action_text' => 'Edit',
    'tags.form.update_btn' => 'Update Tag',
    'tags.form.create_btn' => 'Create Tag',
    'tags.form.notify.updated' => 'Tag updated',
    /**
     * Activity log page.
     */
    'activity_log.index.title' => 'Activity Log',
    /*
     * Product Options
     */
    'product.options.index.title' => 'Options',
    'product.options.index.create_btn' => 'Create Option',
    'product.options.index.table_row_action_text' => 'Edit Option',
    /**
     * Taxes.
     */
    'taxes.tax-zones.index.title' => 'Tax Zones',
    'taxes.tax-zones.confirm_delete.title' => 'Confirm removal',
    'taxes.tax-zones.confirm_delete.message' => 'Are you sure you want to delete this Tax Zone? This could result in data loss.',
    'taxes.tax-zones.customer_groups.title' => 'Restrict to customer groups',
    'taxes.tax-zones.customer_groups.instructions' => 'Select which customer groups you want to restrict this zone to. Leave unchecked for no restrictions.',
    'taxes.tax-zones.create_title' => 'Create tax zone',
    'taxes.tax-zones.create_btn' => 'Create tax zone',
    'taxes.tax-zones.delete_btn' => 'Delete tax zone',
    'taxes.tax-zones.index.table_row_action_text' => 'Manage',
    'taxes.tax-classes.index.title' => 'Tax Classes',
    'taxes.tax-classes.index.create.title' => 'Create tax class',
    'taxes.tax-classes.index.update.title' => 'Update tax class',
    'taxes.tax-classes.create_btn' => 'Create tax class',
    'taxes.tax-zones.price_display.label' => 'Price Display',
    'taxes.tax-zones.price_display.excl_tax' => 'Exclude Tax',
    'taxes.tax-zones.price_display.incl_tax' => 'Include Tax',
    'taxes.tax-zones.zone_type.label' => 'Type',
    'taxes.tax-zones.zone_type.countries' => 'Limit to countries',
    'taxes.tax-zones.zone_type.states' => 'Limit to states / provinces',
    'taxes.tax-zones.zone_type.postcodes' => 'Limit to postcodes',
    'taxes.tax-zones.tax_rates.title' => 'Tax Rates',
    'taxes.tax-zones.tax_rates.create_button' => 'Add tax rate',
    'taxes.tax-zones.save_btn' => 'Save tax zone',
    'taxes.tax-classes.index.delete_message' => 'Are you sure? This could cause loss of data.',
    'taxes.tax-classes.index.delete_message_disabled' => 'You cannot delete a tax class that is associated to product variants',
    'taxes.tax-classes.index.delete_message_default' => 'You must select a new default before deleting',
    /**
     * Customer Groups.
     */
    'customer-groups.index.title' => 'Customer Groups',
    'customer-groups.index.create_btn' => 'Create Customer Group',
    'customer-groups.index.table_row_action_text' => 'Edit Group',
    /**
     * Customer Groups show page.
     */
    'customer-groups.show.title' => 'Edit Customer Group',
    /**
     * Customer Groups create page.
     */
    'customer-groups.create.title' => 'Create Customer Group',
    'customer-groups.form.default_instructions' => 'Set whether this customer group should be the default',
];
