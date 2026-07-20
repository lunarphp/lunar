<?php

namespace Lunar\Core\Actions\Tags;

use Lunar\Core\Contracts\Actions\Tags\CreatesTag;
use Lunar\Core\Models\Tag;

class CreateTag implements CreatesTag
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Tag
    {
        return Tag::create($attributes);
    }
}
