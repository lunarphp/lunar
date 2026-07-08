<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | User code paths
    |--------------------------------------------------------------------------
    |
    | Paths Rector and other code-rewriting steps operate on by default.
    | The `--paths` option on `php artisan lunar:upgrade` overrides this.
    |
    */
    'paths' => [
        app_path(),
        config_path(),
        database_path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Extension class-string rewrites
    |--------------------------------------------------------------------------
    |
    | User-defined class strings persisted in the database that the upgrade
    | tool cannot detect on its own (custom discount conditions, custom
    | purchasables, custom shipping modifiers, etc.). Keys are the v1 class
    | string; values are the v2 replacement.
    |
    */
    'extensions' => [
        'class_strings' => [
            // App\Discounts\MyCondition::class => App\Discounts\MyCondition::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | v1 order headline mapping (spec 0022)
    |--------------------------------------------------------------------------
    |
    | v1's hand-driven `orders.status` values are free-form per store. The
    | order-status data step maps them onto the v2 derived rollups and the
    | open/closed archive:
    |
    | - `fulfilled_statuses`: the order went out the door — it gets
    |   `fulfilment_status` = fulfilled and a whole-order shipped Fulfilment.
    | - `closed_statuses`: the order is archived — `closed_at` is stamped.
    | - `cancelled_statuses`: additionally stamp `cancelled_at`.
    |
    | The defaults cover the stock v1 statuses; add your store's custom
    | statuses to the right lists before running the upgrade.
    |
    */
    'orders' => [
        'fulfilled_statuses' => ['dispatched', 'complete'],
        'closed_statuses' => ['complete', 'cancelled', 'refunded'],
        'cancelled_statuses' => ['cancelled'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migrations ledger rewrite (spec 0003)
    |--------------------------------------------------------------------------
    |
    | After the schema transformation runs, the upgrade tool rewrites the
    | application's `migrations` table to align with the v2 flat baseline.
    |
    | - `v1_match`: regex patterns matched against migration names. Rows whose
    |   `migration` column matches any pattern are removed.
    | - `v2_baseline`: migration names that should be inserted as already-run
    |   so future v2.x migrations layer cleanly. Filled in by follow-up PRs as
    |   the v2 baseline files land in `packages/core/database/migrations`.
    |
    */
    'ledger' => [
        'v1_match' => [
            // v1.x core/admin/shipping migrations span 2021-2026 by their
            // original filename dates. The v2 flat baseline files use the
            // reserved `2026_01_01_*` prefix and are kept intact.
            '/^2021_/',
            '/^2022_/',
            '/^2023_/',
            '/^2024_/',
            '/^2025_/',
            '/^2026_0[2-9]_/',
            '/^2026_1[0-2]_/',
            // Spec 0010: admin staff + permission migrations collapsed into core baseline.
            '/^2026_01_02_000000_create_staff_table$/',
            '/^2026_01_02_000001_create_permission_tables$/',
            '/^2026_01_02_000002_rename_firstname_column_on_staff_table$/',
            '/^2026_01_02_000003_rename_lastname_column_on_staff_table$/',
            '/^2026_01_02_000004_add_two_factor_columns_to_staff_table$/',
            '/^2026_01_02_000005_rename_two_factor_columns_on_staff_table$/',
        ],
        'v2_baseline' => [
            '2026_01_01_000000_create_assets_table',
            '2026_01_01_000001_create_attribute_groups_table',
            '2026_01_01_000002_create_brands_table',
            '2026_01_01_000003_create_channels_table',
            '2026_01_01_000004_create_collection_groups_table',
            '2026_01_01_000005_create_countries_table',
            '2026_01_01_000006_create_currencies_table',
            '2026_01_01_000007_create_customer_groups_table',
            '2026_01_01_000008_create_customers_table',
            '2026_01_01_000009_create_discounts_table',
            '2026_01_01_000010_create_languages_table',
            '2026_01_01_000011_create_media_table',
            '2026_01_01_000012_create_product_options_table',
            '2026_01_01_000013_create_product_types_table',
            '2026_01_01_000014_create_tags_table',
            '2026_01_01_000015_create_tax_classes_table',
            '2026_01_01_000016_create_tax_zones_table',
            '2026_01_01_000017_create_addresses_table',
            '2026_01_01_000018_create_attributes_table',
            '2026_01_01_000019_create_brand_discount_table',
            '2026_01_01_000020_create_channelables_table',
            '2026_01_01_000021_create_collections_table',
            '2026_01_01_000022_create_customer_customer_group_table',
            '2026_01_01_000023_create_customer_discount_table',
            '2026_01_01_000024_create_customer_group_discount_table',
            '2026_01_01_000025_create_customer_user_table',
            '2026_01_01_000026_create_discount_user_table',
            '2026_01_01_000027_create_discountables_table',
            '2026_01_01_000028_create_orders_table',
            '2026_01_01_000029_create_prices_table',
            '2026_01_01_000030_create_product_option_values_table',
            '2026_01_01_000031_create_products_table',
            '2026_01_01_000032_create_states_table',
            '2026_01_01_000033_create_taggables_table',
            '2026_01_01_000034_create_tax_rates_table',
            '2026_01_01_000035_create_tax_zone_countries_table',
            '2026_01_01_000036_create_tax_zone_customer_groups_table',
            '2026_01_01_000037_create_tax_zone_postcodes_table',
            '2026_01_01_000038_create_urls_table',
            '2026_01_01_000039_create_attribute_pivot_tables',
            '2026_01_01_000040_create_brand_collection_table',
            '2026_01_01_000041_create_carts_table',
            '2026_01_01_000042_create_collection_customer_group_table',
            '2026_01_01_000043_create_collection_discount_table',
            '2026_01_01_000044_create_collection_product_table',
            '2026_01_01_000045_create_customer_group_product_table',
            '2026_01_01_000046_create_order_addresses_table',
            '2026_01_01_000047_create_order_lines_table',
            '2026_01_01_000048_create_product_associations_table',
            '2026_01_01_000049_create_product_product_option_table',
            '2026_01_01_000050_create_product_variants_table',
            '2026_01_01_000051_create_tax_rate_amounts_table',
            '2026_01_01_000052_create_tax_zone_states_table',
            '2026_01_01_000053_create_transactions_table',
            '2026_01_01_000054_create_cart_addresses_table',
            '2026_01_01_000055_create_cart_line_discount_table',
            '2026_01_01_000056_create_cart_lines_table',
            '2026_01_01_000057_create_media_product_variant_table',
            '2026_01_01_000058_create_product_option_value_product_variant_table',
            '2026_01_01_000060_create_locations_table',
            '2026_01_01_000061_create_fulfilments_table',
            '2026_01_01_000062_create_fulfilment_lines_table',
            '2026_01_01_000063_create_fulfilment_trackings_table',
            '2026_01_01_000064_create_stock_levels_table',
            '2026_01_01_000065_create_stock_movements_table',
            '2026_01_01_000067_create_regions_table',
            '2026_01_01_000068_create_country_region_table',
            '2026_01_01_000069_add_region_id_to_carts_and_orders',
            '2026_01_01_000059_add_orders_cart_id_foreign_key',
            '2026_01_01_900000_create_staff_table',
            '2026_01_01_900001_create_activity_log_table',
            '2026_01_01_900002_create_permission_tables',
            '2026_01_03_000000_create_shipping_zones_table',
            '2026_01_03_000001_create_shipping_methods_table',
            '2026_01_03_000002_create_shipping_rates_table',
            '2026_01_03_000003_create_shipping_exclusion_lists_table',
            '2026_01_03_000004_create_shipping_exclusions_table',
            '2026_01_03_000005_create_shipping_exclusion_list_shipping_zone_table',
            '2026_01_03_000006_create_country_shipping_zone_table',
            '2026_01_03_000007_create_shipping_zone_postcodes_table',
            '2026_01_03_000008_create_state_shipping_zone_table',
            '2026_01_03_000009_create_order_shipping_zone_table',
            '2026_01_03_000010_remap_shipping_polymorphic_relations',
            '2026_01_03_000011_create_customer_group_shipping_method_table',
            '2026_01_03_000012_switch_shipping_to_jsonb_columns',
            '2026_01_03_000013_add_can_manage_shipping_permission',
            '2026_01_03_000014_add_weight_constraints_to_shipping_methods_table',
            '2026_01_03_000015_remove_cutoff_from_shipping_methods_table',
            '2026_01_03_000016_rescale_weight_based_shipping_min_quantity',
            '2026_01_04_000000_create_stripe_payment_intents_table',
            '2026_01_05_000000_create_opayo_tokens_table',
        ],
    ],

];
