<?php

namespace Lunar\Admin\Support\Facades;

use Illuminate\Support\Facades\Facade;

class LunarPanel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lunar-panel';
    }
}
