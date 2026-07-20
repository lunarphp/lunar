<?php

return [
    'title' => 'Currencies',
    'description' => 'Currencies your store prices and transacts in.',
    'create_currency' => 'Create currency',
    'create_description' => 'Add a currency your store can price and transact in.',
    'default_badge' => 'Default',
    'empty_title' => 'No currencies',

    'column_code' => 'Code',
    'column_name' => 'Name',
    'column_exchange_rate' => 'Exchange rate',

    'field_code' => 'Code',
    'code_hint' => 'ISO 4217',
    'field_name' => 'Name',
    'name_placeholder' => 'e.g. Pound Sterling',
    'field_exchange_rate' => 'Exchange rate',
    'field_decimal_places' => 'Decimal places',
    'default_currency' => 'Default currency',
    'default_currency_hint' => 'Used for store accounting and the default storefront price.',
    'default_locked_hint' => 'This is the default currency. To change it, make another currency the default.',
    'default_unset_blocked' => 'The default currency cannot be unset. Make another currency the default instead.',
    'default_disable_blocked' => 'The default currency cannot be disabled. Make another currency the default first.',
    'enabled_hint' => "When off, customers can't transact in this currency.",
    'enabled_locked_hint' => 'The default currency is always enabled.',
    'sync_prices' => 'Sync prices',
    'sync_prices_hint' => 'Generate prices in this currency from the default currency using the exchange rate.',

    'section_details' => 'Details',
    'section_state' => 'State',
    'edit_title' => 'Edit currency — {code}',

    'confirm_delete_currency' => 'Are you sure you want to delete this currency?',
    'confirm_delete_title' => 'Delete currency?',
    'confirm_delete_body' => '{code} will be permanently removed.',
    'delete_blocked' => 'Cannot delete a currency with prices.',
    'delete_blocked_default' => 'The default currency cannot be deleted. Make another currency the default first.',

    'flash_created' => 'Currency created.',
    'flash_updated' => 'Currency updated.',
    'flash_deleted' => 'Currency deleted.',
];
