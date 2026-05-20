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
            // v1.x core/admin/shipping migrations span 2021–2026 by their
            // original filename dates. The v2 flat baseline files use the
            // reserved `2026_01_01_*` prefix and are kept intact.
            '/^2021_/',
            '/^2022_/',
            '/^2023_/',
            '/^2024_/',
            '/^2025_/',
            '/^2026_0[2-9]_/',
            '/^2026_1[0-2]_/',
        ],
        'v2_baseline' => [
            '2026_01_01_000000_create_channels_table',
            '2026_01_01_000001_create_languages_table',
            '2026_01_01_000002_create_channelables_table',
            '2026_01_01_000003_create_currencies_table',
            '2026_01_01_000004_create_attribute_groups_table',
            '2026_01_01_000005_create_attributes_table',
            '2026_01_01_000006_create_attributables_table',
            '2026_01_01_000007_create_product_types_table',
            '2026_01_01_000008_create_tax_classes_table',
            '2026_01_01_000009_create_tax_zones_table',
            '2026_01_01_000010_create_products_table',
            '2026_01_01_000011_create_product_associations_table',
            '2026_01_01_000012_create_product_variants_table',
            '2026_01_01_000013_create_customer_groups_table',
            '2026_01_01_000014_create_customer_group_product_table',
            '2026_01_01_000015_create_customers_table',
            '2026_01_01_000016_create_customer_customer_group_table',
            '2026_01_01_000017_create_customer_user_table',
            '2026_01_01_000018_create_prices_table',
            '2026_01_01_000019_create_countries_table',
            '2026_01_01_000020_create_states_table',
            '2026_01_01_000021_create_addresses_table',
            '2026_01_01_000022_create_tax_zone_countries_table',
            '2026_01_01_000023_create_tax_zone_states_table',
            '2026_01_01_000024_create_tax_zone_postcodes_table',
            '2026_01_01_000025_create_tax_zone_customer_groups_table',
            '2026_01_01_000026_create_tax_rates_table',
            '2026_01_01_000027_create_tax_rate_amounts_table',
            '2026_01_01_000028_create_collection_groups_table',
            '2026_01_01_000029_create_collections_table',
            '2026_01_01_000030_create_collection_product_table',
            '2026_01_01_000031_create_collection_customer_group_table',
            '2026_01_01_000032_create_product_options_table',
            '2026_01_01_000033_create_product_option_values_table',
            '2026_01_01_000034_create_product_option_value_product_variant_table',
            '2026_01_01_000035_create_tags_table',
            '2026_01_01_000036_create_taggables_table',
            '2026_01_01_000037_create_urls_table',
            '2026_01_01_000038_create_orders_table',
            '2026_01_01_000039_create_order_lines_table',
            '2026_01_01_000040_create_order_addresses_table',
            '2026_01_01_000041_create_transactions_table',
            '2026_01_01_000042_create_carts_table',
            '2026_01_01_000043_create_cart_addresses_table',
            '2026_01_01_000044_create_cart_lines_table',
            '2026_01_01_000045_create_assets_table',
            '2026_01_01_000046_create_media_product_variant_table',
            '2026_01_01_000047_create_brands_table',
            '2026_01_01_000048_create_discounts_table',
            '2026_01_01_000049_create_cart_line_discount_table',
            '2026_01_01_000050_create_brand_discount_table',
            '2026_01_01_000051_create_customer_group_discount_table',
            '2026_01_01_000052_create_collection_discount_table',
            '2026_01_01_000053_create_discount_user_table',
            '2026_01_01_000054_create_product_product_option_table',
            '2026_01_01_000055_create_brand_collection_table',
            '2026_01_01_000056_create_customer_discount_table',
            '2026_01_01_000057_create_discountables_table',
            '2026_01_01_900000_create_media_table',
            '2026_01_01_900001_create_activity_log_table',
            '2026_01_01_999000_switch_to_jsonb',
        ],
    ],

];
