<?php

namespace Lunar\Core\Contracts\Actions\Tags;

use Lunar\Core\Models\Tag;

interface DeletesTag
{
    public function execute(Tag $tag): void;
}
