<?php

return [
    'products.option-manager.add_btn'            => 'Add new option',
    'products.option-manager.toggle_btn'         => 'Toggle All',
    'products.product-selector.select_btn'       => 'Select Options',
    'products.product-selector.title'            => 'Select Options',
    'products.product-selector.available_tab'    => 'Available options',
    'products.product-selector.selected_tab'     => 'Selected options',
    'products.product-selector.selected_empty'   => 'Unable to find any options with the given search term.',
    'products.product-selector.no_results'       => 'Unable to find any options with the given search term.',
    'products.product-selector.no_options'       => 'There are no options available, create a new option to see it here.',
    'products.product-selector.add_new_btn'      => 'Add new option',
    'products.product-selector.use_selected_btn' => 'Use selected options',
    'products.product-selector.title'            => 'Select Options',
    'products.option-creator.option_placeholder' => 'E.g. Colour',
    'products.option-creator.value_placeholder'  => 'E.g. Blue',
    'products.option-creator.min_values_notice'  => 'You must have at least :min value',
    'products.option-creator.values_title'       => 'Product option values',
    'products.option-creator.title'              => 'Create new option',
    'products.option-creator.add_value_btn'      => 'Add value',
    'products.option-creator.create_option_btn'  => 'Create option',
    'products.option-creator.values_strapline'   => 'Add all the different possible values that are available for this product option.',
    'product-search.btn'                         => 'Add Products',
    'product-search.first_tab'                   => 'Search products',
    'product-search.second_tab'                  => 'Selected products',
    'product-search.max_results_exceeded'        => 'Showing the first :max of :total products. Try being more specific in your search.',
    'product-search.exists_in_collection'        => 'Already associated',
    'product-search.no_results'                  => 'No results found.',
    'product-search.pre_search_message'          => 'Search for products by attribute or SKU.',
    'product-search.select_empty'                => 'When you select products, they will appear here.',
    'product-search.title'                       => 'Search for products',
    'product-search.associate_self'              => 'You cannot associate the same product',
    'product-search.commit_btn'                  => 'Select Products',
    /**
     * Option Value Create Modal.
     */
    'ovcm.title' => 'Add new option to :name',
    /**
     * Attribute group create.
     */
    'attribute-group-edit.name.placeholder'     => 'e.g. Additional Details',
    'attribute-group-edit.create_btn'           => 'Create attribute group',
    'attribute-group-edit.update_btn'           => 'Update attribute group',
    'attribute-group-edit.non_unique_handle'    => 'This name has been already been taken',
    /**
     * Attribute show.
     */
    'attributes.show.create_group_btn'           => 'Create attribute group',
    'attributes.show.create_attribute'           => 'Create attribute',
    'attributes.show.edit_group_btn'             => 'Edit attribute group',
    'attributes.show.edit_attribute_btn'         => 'Edit attribute',
    'attributes.show.delete_group_btn'           => 'Delete attribute group',
    'attributes.show.edit_title'                 => 'Edit attribute group',
    'attributes.show.create_title'               => 'Create attribute group',
    'attributes.show.delete_title'               => 'Delete attribute group',
    'attributes.show.delete_warning'             => 'Removing this customer group will also remove all attributes associated to it. This action cannot be reversed.',
    'attributes.show.group_protected'            => 'This group contains attributes required by the system so cannot be removed.',
    'attributes.show.no_attributes_text'         => 'No attributes exist, either drag existing attributes or add new ones here.',
    'attributes.show.delete_attribute_btn'       => 'Delete attribute',
    'attributes.show.delete_attribute_title'     => 'Delete Attribute',
    'attributes.show.delete_attribute_warning'   => 'Are you sure you want to remove this attribute?',
    'attributes.show.delete_attribute_protected' => 'You cannot delete a system attribute.',
    'attributes.show.no_groups'                  => 'No attribute groups found, add your first one before you can add attributes to it.',
    /**
     * Attribute edit.
     */
    'attribute-edit.create_title'            => 'Create attribute',
    'attribute-edit.update_title'            => 'Update attribute',
    'attribute-edit.system_locked'           => 'This attribute is required by the system so some fields are disabled.',
    'attribute-edit.name.placeholder'        => 'e.g. Name',
    'attribute-edit.required.instructions'   => 'Is this attribute required when editing/creating?',
    'attribute-edit.searchable.instructions' => 'Should users be able to search via this attribute?',
    'attribute-edit.filterable.instructions' => 'Should users be able to filter results based on this attribute?',
    'attribute-edit.validation.instructions' => 'Specify any Laravel validation rules for this input.',

    /**
     * Collection search.
     */
    'collection-search.btn'                         => 'Add Collections',
    'collection-search.first_tab'                   => 'Search collections',
    'collection-search.second_tab'                  => 'Selected collections',
    'collection-search.max_results_exceeded'        => 'Showing the first :max of :total collections. Try being more specific in your search.',
    'collection-search.exists_in_collection'        => 'Already associated',
    'collection-search.no_results'                  => 'No results found.',
    'collection-search.pre_search_message'          => 'Search for collections by attribute.',
    'collection-search.select_empty'                => 'When you select collections, they will appear here.',
    'collection-search.title'                       => 'Search for collections',
    'collection-search.commit_btn'                  => 'Select Collections',
    /**
     * Order Show.
     */
    'orders.show.title'                             => 'Order',
    'orders.show.save_shipping_btn'                 => 'Save Address',
    'orders.show.save_billing_btn'                 => 'Save Address',
    'orders.show.print_btn'                         => 'Print',
    'orders.show.refund_btn'                         => 'Refund',
    'orders.show.refund_lines_btn'                  => 'Refund Lines',
    'orders.show.update_status_btn'                         => 'Update Status',
    'orders.show.more_actions_btn'                         => 'More Actions',
    'orders.show.show_all_lines_btn' => 'Show all lines',
    'orders.show.collapse_lines_btn' => 'Collapse lines',
    'orders.show.transactions_header' => 'Transactions',
    'orders.show.timeline_header' => 'Timeline',
    'orders.show.additional_fields_header' => 'Additional Information',
    'orders.show.billing_matches_shipping' => 'Same as shipping address',
    'orders.show.billing_header' => 'Billing Address',
    'orders.show.shipping_header' => 'Shipping Address',
    /**
     * Order Refund.
     */
    'orders.refund.confirm_text'                    => 'CONFIRM',
    'orders.refund.confirm_message'                 => 'Please type :confirm to confirm you want to send the refund',
    'orders.show.print_btn'                         => 'Print',
    'orders.show.refund_btn'                         => 'Refund',
    'orders.show.refund_lines_btn'                  => 'Refund Lines',
    'orders.show.update_status_btn'                         => 'Update Status',
    'orders.show.more_actions_btn'                         => 'More Actions',
    'orders.show.show_all_lines_btn' => 'Show all lines',
    'orders.show.collapse_lines_btn' => 'Collapse lines',
    'orders.show.transactions_header' => 'Transactions',
    'orders.show.timeline_header' => 'Timeline',
    'orders.show.additional_fields_header' => 'Additional Information',
    'orders.show.billing_matches_shipping' => 'Same as shipping address',
    'orders.show.billing_header' => 'Billing Address',
    'orders.show.shipping_header' => 'Shipping Address',
    'orders.show.requires_capture' => 'This order still requires payment to be captured.',
    'orders.show.capture_payment_btn' => 'Capture Payment',
    /**
     * Order Refund.
     */
    'orders.refund.confirm_text'                    => 'CONFIRM',
    'orders.refund.confirm_message'                 => 'Please type :confirm to confirm you want to send the refund',
    'orders.refund.no_charges'                      => 'There are no refundable charges on this order',
    'orders.refund.select_transaction'              => 'Select a transaction',
    'orders.refund.refund_btn'                      => 'Send refund',
    'orders.refund.fully_refunded'                  => 'The captures on this order have been refunded',
    /**
     * Order Capture.
     */
    'orders.capture.confirm_text'                    => 'CONFIRM',
    'orders.capture.confirm_message'                 => 'Please confirm you want to capture this payment',
    'orders.capture.no_intents'                      => 'There are no transactions available for capture',
    'orders.capture.select_transaction'              => 'Select a transaction',
    'orders.capture.capture_btn'                      => 'Capture payment',
];
