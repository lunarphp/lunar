<?php

namespace Lunar\Admin\Support\Pages\Concerns;

trait ExtendsHeaderWidgets
{
    protected function getDefaultHeaderWidgets(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return $this->callLunarHook('headerWidgets', $this->getDefaultHeaderWidgets());
    }
}
