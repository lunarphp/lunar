<?php

namespace Lunar\Core\Contracts\Actions\Tags;

use Lunar\Core\Models\Tag;

interface CreatesTag
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Tag;
}
