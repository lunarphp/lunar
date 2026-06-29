<?php

namespace Lunar\Core\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Core\Models\Customer;

interface LunarUser
{
    public function customers(): BelongsToMany;

    public function carts(): HasMany;

    public function latestCustomer(): ?Customer;

    public function orders(): HasMany;
}
