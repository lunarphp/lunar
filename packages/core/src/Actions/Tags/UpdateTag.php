<?php

namespace Lunar\Core\Actions\Tags;

use Lunar\Core\Contracts\Actions\Tags\UpdatesTag;
use Lunar\Core\Models\Tag;

class UpdateTag implements UpdatesTag
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Tag $tag, array $attributes): Tag
    {
        $tag->update($attributes);

        return $tag;
    }
}
