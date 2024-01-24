<?php

namespace Lunar\Admin\Support\Extending;

abstract class ViewPageExtension extends BaseExtension
{
    public function headerActions(array $actions): array
    {
        return $actions;
    }
}
