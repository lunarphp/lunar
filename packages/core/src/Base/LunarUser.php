<?php

namespace Lunar\Base;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Models\Contracts\Customer;

interface LunarUser
{
    public function customers(): BelongsToMany;

    public function carts(): HasMany;

    public function latestCustomer(): ?Customer;

    public function orders(): HasMany;
}
