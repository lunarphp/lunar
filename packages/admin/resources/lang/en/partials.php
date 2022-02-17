<?php

return [
    /**
     * Activity Log.
     */
    'activity-log.added_images.description' => 'Added :count images',
    /**
     * Availability.
     */
    'availability.heading'                                               => 'Availability',
    'availability.schedule_notice'                                       => "When you schedule availability, that :type won't be available for the channel/customer group until the date has past and the :type is active.",
    'availability.channel_heading'                                       => 'Channels',
    'availability.channel_strapline'                                     => 'Select which channels this :type is available on.',
    'availability.channels.hidden'                                       => 'Hidden',
    'availability.channels.purchasable'                                  => 'Purchasable',
    'availability.channels.strapline'                                    => 'Schedule which customer groups this :type is available for.',
    'availability.channels.scheduled_from'                               => 'Scheduled from :datetime',
    'availability.channels.scheduled_to'                                 => 'Available until :datetime',
    'availability.channels.scheduled_range'                              => ':from until :to',
    'availability.channels.scheduled_always'                             => 'Always available',
    'availability.channels.scheduled_never'                              => 'Never available',
    'availability.channels.schedule_modal.title'                         => 'Schedule availability',
    'availability.channels.schedule_modal.starts_at.label'               => 'Starts at',
    'availability.channels.schedule_modal.starts_at.instructions'        => 'Set when this customer group will be available from, no date indicates always available.',
    'availability.channels.schedule_modal.ends_at.label'                 => 'Ends at',
    'availability.channels.schedule_modal.ends_at.instructions'          => 'Set when this customer group will be available until, no date indicates always available.',
    'availability.channels.schedule_modal.btn_text'                      => 'Accept & Close',
    'availability.scheduled_text'                                        => 'This :type is scheduled to be published on :date.',
    'availability.schedule_placeholder'                                  => 'Schedule publish date.',
    'availability.schedule_btn_text'                                     => 'Schedule availability',
    'availability.clear_btn'                                             => 'Clear',
    'availability.customer_groups.title'                                 => 'Customer groups',
    'availability.customer_groups.visible'                               => 'Visible',
    'availability.customer_groups.hidden'                                => 'Hidden',
    'availability.customer_groups.purchasable'                           => 'Purchasable',
    'availability.customer_groups.strapline'                             => 'Schedule which customer groups this :type is available for.',
    'availability.customer_groups.scheduled_from'                        => 'Scheduled from :datetime',
    'availability.customer_groups.scheduled_to'                          => 'Available until :datetime',
    'availability.customer_groups.scheduled_range'                       => ':from until :to',
    'availability.customer_groups.scheduled_always'                      => 'Always available',
    'availability.customer_groups.scheduled_never'                       => 'Never available',
    'availability.customer_groups.schedule_modal.title'                  => 'Schedule availability',
    'availability.customer_groups.schedule_modal.starts_at.label'        => 'Starts at',
    'availability.customer_groups.schedule_modal.starts_at.instructions' => 'Set when this customer group will be available from, no date indicates always available.',
    'availability.customer_groups.schedule_modal.ends_at.label'          => 'Ends at',
    'availability.customer_groups.schedule_modal.ends_at.instructions'   => 'Set when this customer group will be available until, no date indicates always available.',
    'availability.customer_groups.schedule_modal.btn_text'               => 'Accept & Close',
    /**
     * Basic Information.
     */
    'products.basic-information.heading' => 'Basic Information',
    /**
     * Image Manager.
     */
    'image-manager.generic_upload_error'             => 'There was a problem uploading, please check you only selected images.',
    'image-manager.heading'                          => 'Images',
    'image-manager.download_original_btn'            => 'Download Original',
    'image-manager.remake_transforms'                => 'Remake Transforms',
    'image-manager.remake_transforms.notify.success' => 'Image transforms have been regenerated',
    'image-manager.save_btn'                         => 'Save image',
    'image-manager.edit_row_btn'                     => 'Edit',
    'image-manager.delete_row_btn'                   => 'Delete',
    'image-manager.delete_message'                   => 'This image will be deleted on save,',
    'image-manager.undo_btn'                         => 'undo',
    'image-manager.no_results'                       => 'No images exist for this product, add your first image above.',
    /**
     * Product Collections.
     */
    'products.collections.heading'                  => 'Collections',
    'products.collections.view_collection'          => 'View Collection',
    /**
     * Product Status Bar.
     */
    'products.status-bar.published.label'       => 'Published',
    'products.status-bar.published.description' => 'This product will be available across all enabled channels and customer groups.',
    'products.status-bar.draft.label'           => 'Draft',
    'products.status-bar.draft.description'     => 'This product will be hidden from all channels and customer groups.',
    /**
     * Variants.
     */
    'products.variants.heading'               => 'Variants',
    'products.variants.strapline'             => 'This product has multiple options, like different sizes or colors.',
    'products.variants.table_row_action_text' => 'Edit',
    'products.variants.table_row_delete_text' => 'Delete',
    /**
     * Product type.
     */
    'product-type.available_title'              => 'Available Attributes',
    'product-type.selected_title'               => 'Selected Attributes (:count)',
    'product-type.attribute_search_placeholder' => 'Search for an attribute by name',
    'product-type.attribute_system_required'    => 'This attribute is required by the system',
    /**
     * Pricing.
     */
    'pricing.title'                      => 'Pricing',
    'pricing.customer_groups.title'      => 'Customer group pricing',
    'pricing.customer_groups.strapline'  => 'Determines if you would like different pricing across customer groups.',
    'pricing.tiers.title'                => 'Tiered Pricing',
    'pricing.tiers.strapline'            => 'Tired pricing allows you to offer discounted pricing based on units sold.',
    'pricing.non_default_currency_alert' => 'Some fields can only be changed when using the default currency.',
    'pricing.tiers.add_tier_btn'         => 'Add Tier',
    /**
     * Indentifiers.
     */
    'identifiers.title' => 'Product Identifiers',
    /**
     * URLs.
     */
    'urls.title'      => 'URLs',
    'urls.create_btn' => 'Add URL',
    /**
     * Inventory.
     */
    'inventory.title'                 => 'Inventory',
    'inventory.maintenance_notice'    => 'This section is still under development and is likely to change in an upcoming release.',
    'inventory.options.in_stock'      => 'In stock',
    'inventory.options.always'        => 'Always',
    'inventory.options.backorder'     => 'Backorder',
    'inventory.purchasable.in_stock'  => 'This item can only be bought when in stock.',
    'inventory.purchasable.always'    => 'This item can always be purchased.',
    'inventory.purchasable.backorder' => 'This item can be bought when stock is expected.',
    /**
     * Shipping.
     */
    'shipping.title'             => 'Shipping',
    'shipping.calculated_volume' => 'Calculated as :value.',
    'shipping.manual_volume_btn' => 'Click to set manually',
    'shipping.auto_volume_btn'   => 'Use generated volume',
];
