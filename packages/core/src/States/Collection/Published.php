<?php

namespace Lunar\Core\States\Collection;

class Published extends CollectionState
{
    public static string $name = 'published';

    public function label(): string
    {
        return __('lunar::states.collection.published');
    }
}
