<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Tag
{
    public function taggable(): MorphTo;
}
