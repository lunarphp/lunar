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
            '/^2021_/',
            '/^2022_/',
            '/^2023_/',
            '/^2024_/',
            '/^2025_/',
            '/^2026_(0[1-4])_.*_(create|add|alter|update|drop|rename|set|populate|migrate|ensure|convert)_.*$/',
        ],
        'v2_baseline' => [
            // '2026_01_01_000000_create_channels_table',
            // …populated as the flat baseline files land.
        ],
    ],

];
