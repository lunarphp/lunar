<?php

namespace Lunar\Admin\Support\Pages\Concerns;

trait ExtendsHeaderActions
{
    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return $this->callLunarHook('headerActions', $this->getDefaultHeaderActions());
    }
}
