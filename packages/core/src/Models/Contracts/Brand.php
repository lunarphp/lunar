<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string $name
 * @property ?Collection $description
 * @property ?Collection $short_description
 */
interface Brand
{
    /**
     * Return the product relationship.
     */
    public function products(): HasMany;
}
