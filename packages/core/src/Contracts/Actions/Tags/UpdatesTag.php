<?php

namespace Lunar\Core\Contracts\Actions\Tags;

use Lunar\Core\Models\Tag;

interface UpdatesTag
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Tag $tag, array $attributes): Tag;
}
