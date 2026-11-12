<?php

namespace Lunar\Core\Actions\Discounts;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Contracts\Actions\Discounts\CountsCustomerRedemptions;
use Lunar\Core\Contracts\Actions\Discounts\ResolvesCustomerEmail;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Discount;

/**
 * Counts by user or email, so a customer can't reset the limit by signing out.
 */
class CountCustomerRedemptions implements CountsCustomerRedemptions
{
    public function __construct(
        protected ResolvesCustomerEmail $resolvesCustomerEmail,
    ) {}

    public function execute(Discount $discount, Cart $cart): int
    {
        $userId = $cart->user_id;
        $email = $this->resolvesCustomerEmail->execute($cart);

        if (! $userId && ! $email) {
            return 0;
        }

        return $discount->redemptions()
            ->where(function (Builder $query) use ($userId, $email) {
                $query->when($userId, fn (Builder $query) => $query->orWhere('user_id', $userId))
                    ->when($email, fn (Builder $query) => $query->orWhere('email', $email));
            })
            ->count();
    }
}
