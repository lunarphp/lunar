<?php

namespace Lunar\Opayo\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Opayo\Models\OpayoToken;

trait HasOpayoTokens
{
    public function opayoTokens(): HasMany
    {
        return $this->hasMany(OpayoToken::class);
    }
}
