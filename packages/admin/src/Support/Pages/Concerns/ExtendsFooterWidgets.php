<?php

namespace Lunar\Admin\Support\Pages\Concerns;

trait ExtendsFooterWidgets
{
    protected function getDefaultFooterWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return $this->callLunarHook('footerWidgets', $this->getDefaultFooterWidgets());
    }
}
