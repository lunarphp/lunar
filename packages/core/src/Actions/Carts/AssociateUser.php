<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Lunar\Base\LunarUser;
use Lunar\Models\Cart;

class AssociateUser extends AbstractAction
{
    /**
     * Execute the action
     *
     * @param  string  $policy
     */
    public function execute(Cart $cart, LunarUser $user, $policy = 'merge'): self
    {
        if ($policy == 'merge') {
            $userCart = Cart::whereUserId($user->getKey())->active()->unMerged()->latest()->first();
            if ($userCart) {
                app(MergeCart::class)->execute($userCart, $cart);
            }
        }

        if ($policy == 'override') {
            $userCart = Cart::whereUserId($user->getKey())->active()->unMerged()->latest()->first();
            if ($userCart && $userCart->id != $cart->id) {
                $userCart->update([
                    'merged_id' => $userCart->id,
                ]);
            }
        }

        $cart->update([
            'user_id' => $user->getKey(),
            'customer_id' => $user->latestCustomer()?->getKey(),
        ]);

        return $this;
    }
}
