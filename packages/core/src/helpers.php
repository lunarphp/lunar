<?php

use GetCandy\Base\Traits\GetCandyUser;

if (! function_exists('is_getcandy_user')) {
    function is_getcandy_user($user)
    {
        $traits = $traits = class_uses_recursive($user);
        return in_array(GetCandyUser::class, $traits);
    }
}
