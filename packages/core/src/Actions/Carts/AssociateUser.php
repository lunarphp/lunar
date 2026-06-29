<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\AssociatesUser;
use Lunar\Core\Contracts\LunarUser;
use Lunar\Core\Models\Cart;

class AssociateUser implements AssociatesUser
{
    public function __construct(
        protected MergeCart $mergeCart,
    ) {}

    /**
     * Execute the action.
     */
    public function execute(Cart $cart, LunarUser $user, string $policy = 'merge'): void
    {
        if ($policy === 'merge') {
            $userCart = Cart::whereUserId($user->getKey())->active()->unMerged()->latest()->first();

            if ($userCart) {
                $this->mergeCart->execute($cart, $userCart);
            }
        }

        if ($policy === 'override') {
            $userCart = Cart::whereUserId($user->getKey())->active()->unMerged()->latest()->first();

            if ($userCart && $userCart->id !== $cart->id) {
                $userCart->update([
                    'merged_id' => $userCart->id,
                ]);
            }
        }

        $cart->update([
            'user_id' => $user->getKey(),
            'customer_id' => $user->latestCustomer()?->getKey(),
        ]);
    }
}
