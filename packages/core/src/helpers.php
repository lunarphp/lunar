<?php

use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Concerns\IsLunarUser;

if (! function_exists('is_lunar_user')) {
    function is_lunar_user($user)
    {
        $traits = class_uses_recursive($user);

        return in_array(IsLunarUser::class, $traits);
    }
}

if (! function_exists('prices_inc_tax')) {
    function prices_inc_tax()
    {
        return config('lunar.pricing.stored_inclusive_of_tax', false);
    }
}

if (! function_exists('can_drop_foreign_keys')) {
    function can_drop_foreign_keys(): bool
    {
        // FK dropping in SQLite was added in this version.
        return DB::getDriverName() !== 'sqlite' || app()->version() >= '11.15.0';
    }
}
