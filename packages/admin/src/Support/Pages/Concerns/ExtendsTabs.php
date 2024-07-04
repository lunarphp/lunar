<?php

namespace Lunar\Admin\Support\Pages\Concerns;

trait ExtendsTabs
{
    protected function getDefaultTabs(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return $this->callLunarHook('getTabs', $this->getDefaultTabs());
    }
}
