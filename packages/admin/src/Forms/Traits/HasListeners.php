<?php

namespace Lunar\Hub\Forms\Traits;

trait HasListeners
{
    public function getListeners()
    {
        return array_merge($this->listeners, [
            'onCreateForm' => 'create',
            'onUpdateForm' => 'update',
        ]);
    }
}
