<?php

use Lunar\Base\Traits\LunarUser;

if (! function_exists('is_lunar_user')) {
    function is_lunar_user($user)
    {
        $traits = class_uses_recursive($user);

        return in_array(LunarUser::class, $traits);
    }
}

if (! function_exists('prices_inc_tax')) {
    function prices_inc_tax()
    {
        return config('lunar.pricing.stored_inclusive_of_tax', false);
    }
}

/**
 * Generate a raw DB query to search for a JSON field.
 *
 * @param  array|string  $json
 *
 * @throws \Exception
 *
 * @return \Illuminate\Database\Query\Builder
 */
if (! function_exists('cast_to_json')) {
    function cast_to_json($json)
    {
        // Convert from array to json and add slashes, if necessary.
        if (is_array($json)) {
            $json = addslashes(json_encode($json));
        } // Or check if the value is malformed.
        elseif (is_null($json) || is_null(json_decode($json))) {
            throw new \Exception('A valid JSON string was not provided.');
        }
        return \DB::raw("CAST('{$json}' AS JSON)");
    }
}
