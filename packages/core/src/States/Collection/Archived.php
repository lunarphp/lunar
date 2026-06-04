<?php

namespace Lunar\Core\States\Collection;

class Archived extends CollectionState
{
    public static string $name = 'archived';

    public function label(): string
    {
        return __('lunar::states.collection.archived');
    }
}
