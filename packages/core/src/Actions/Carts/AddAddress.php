<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\AddsAddress;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;

class AddAddress implements AddsAddress
{
    protected array $fillableAttributes = [
        'country_id',
        'title',
        'first_name',
        'last_name',
        'company_name',
        'tax_identifier',
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
        Cart $cart,
        array|Addressable $address,
        string $type
    ): void {
        /** @var Cart $cart */
        // Do we already have an address for this type?
        $cart->addresses()->whereType($type)->delete();

        if (is_array($address)) {
            $cartAddress = new CartAddress($address);
        }

        if ($address instanceof Addressable) {
            $cartAddress = new CartAddress(
                $address->only($this->fillableAttributes)
            );
        }

        // Force the type.
        $cartAddress->type = $type;
        $cartAddress->cart_id = $cart->id;
        $cartAddress->save();
    }
}
