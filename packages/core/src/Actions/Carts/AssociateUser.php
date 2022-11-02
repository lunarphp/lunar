<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Illuminate\Foundation\Auth\User;
use Lunar\Models\Cart;

class AssociateUser extends AbstractAction
{
    /**
     * Execute the action
     *
     * @param Cart $cart
     * @param User $user
     * @param string $policy
     *
     * @return self
     */
    public function execute(Cart $cart, User $user, $policy = 'merge'): self
    {
        if ($policy == 'merge') {
            $userCart = Cart::whereUserId($user->getKey())->unMerged()->latest()->first();
            if ($userCart) {
                app(MergeCart::class)->execute($userCart, $cart);
            }
        }

        if ($policy == 'override') {
            $userCart = Cart::whereUserId($user->getKey())->unMerged()->latest()->first();
            if ($userCart && $userCart->id != $cart->id) {
                $userCart->update([
                    'merged_id' => $userCart->id,
                ]);
            }
        }

        $cart->update([
            'user_id' => $user->getKey(),
        ]);

        return $this;
    }
}
