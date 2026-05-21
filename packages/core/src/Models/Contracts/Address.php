<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Address
{
    public function country(): BelongsTo;

    public function customer(): BelongsTo;
}
