<?php

namespace Lunar\Admin\Support\Extending;

abstract class BaseExtension
{
    protected ?object $caller;

    public function setCaller(?object $caller): void
    {
        $this->caller = $caller;
    }

    public function headerActions(array $actions): array
    {
        return $actions;
    }
}
