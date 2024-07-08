<?php

namespace Lunar\Admin\Support\Extending;

abstract class BaseExtension
{
    protected object|null $caller;

    public function setCaller(object|null $caller): void
    {
        $this->caller = $caller;
    }

    public function headerActions(array $actions): array
    {
        return $actions;
    }
}
