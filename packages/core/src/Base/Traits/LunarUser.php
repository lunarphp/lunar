<?php

namespace Lunar\Core\Base\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Order;

trait LunarUser
{
    public function customers(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(Customer::class, "{$prefix}customer_user");
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function latestCustomer(): ?Customer
    {
        return $this->customers()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
    }

    /**
     * Return the user orders relationship.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
