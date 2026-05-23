<?php

use Lunar\Core\Models\Staff;

return [
    /*
    |--------------------------------------------------------------------------
    | Staff auth guard
    |--------------------------------------------------------------------------
    |
    | The auth guard name used for staff accounts. Both Filament and the
    | future Inertia panel resolve permissions/policies against this guard.
    |
    */
    'guard' => 'staff',

    /*
    |--------------------------------------------------------------------------
    | Staff auth provider
    |--------------------------------------------------------------------------
    |
    | The eloquent provider name and model used to look up staff accounts.
    | Override the model when extending the Lunar Staff class downstream.
    |
    */
    'provider' => 'staff',

    'model' => Staff::class,

    /*
    |--------------------------------------------------------------------------
    | Register guard automatically
    |--------------------------------------------------------------------------
    |
    | When true Lunar will register the staff guard and provider on boot. Set
    | to false to manage guard/provider configuration manually in auth.php.
    |
    */
    'register_guard' => true,
];
