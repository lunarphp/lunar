<?php

namespace Lunar\Core\States\Collection;

class Draft extends CollectionState
{
    public static string $name = 'draft';

    public function label(): string
    {
        return __('lunar::states.collection.draft');
    }
}
