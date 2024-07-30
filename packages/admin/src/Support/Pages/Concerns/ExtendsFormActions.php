<?php

namespace Lunar\Admin\Support\Pages\Concerns;

trait ExtendsFormActions
{
    protected function getDefaultFormActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            ...$this->callLunarHook('formActions', $this->getDefaultFormActions()),
        ];
    }
}
