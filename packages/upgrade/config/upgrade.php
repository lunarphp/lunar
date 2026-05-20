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

];
