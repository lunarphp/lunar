<?php

namespace Lunar\Search;

if (! function_exists('data_path')) {
    function data_path(): string
    {
        return __DIR__.'/Data';
    }
}
