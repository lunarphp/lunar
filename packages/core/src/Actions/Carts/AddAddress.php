<?php

namespace Lunar\Actions\Carts;

use Lunar\Actions\AbstractAction;
use Lunar\Base\Addressable;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Contracts\Cart as CartContract;

class AddAddress extends AbstractAction
{
    protected $fillableAttributes = [
        'country_id',
        'title',
        'first_name',
        'last_name',
        'company_name',
        'line_one',
        'line_two',
        'line_three',
        'city',
        'state',
        'postcode',
        'delivery_instructions',
        'contact_email',
        'contact_phone',
        'meta',
    ];

    /**
     * Execute the action.
     */
    public function execute(
        CartContract $cart,
        array|Addressable $address,
        string $type
    ): self {
        /** @var Cart $cart */
        // Do we already have an address for this type?
        $cart->addresses()->whereType($type)->delete();

        if (is_array($address)) {
            $cartAddress = new (CartAddress::modelClass())($address);
        }

        if ($address instanceof Addressable) {
            $cartAddress = new (CartAddress::modelClass())(
                $address->only($this->fillableAttributes)
            );
        }

        // Force the type.
        $cartAddress->type = $type;
        $cartAddress->cart_id = $cart->id;
        $cartAddress->save();

        return $this;
    }
}
