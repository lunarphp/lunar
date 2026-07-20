<?php

namespace Lunar\Core\Actions\Tags;

use Lunar\Core\Contracts\Actions\Tags\DeletesTag;
use Lunar\Core\Models\Tag;

/**
 * Delete a tag. The model's deleting hook removes its taggable pivot rows, so
 * tagged records are simply untagged.
 */
class DeleteTag implements DeletesTag
{
    public function execute(Tag $tag): void
    {
        $tag->delete();
    }
}
