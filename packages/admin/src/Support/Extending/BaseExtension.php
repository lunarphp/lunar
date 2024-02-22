<?php

namespace Lunar\Admin\Support\Extending;

abstract class BaseExtension
{
    public function headerActions(array $actions): array
    {
        return $actions;
    }
}
