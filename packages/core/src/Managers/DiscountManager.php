<?php

namespace GetCandy\Managers;

use GetCandy\Base\CartLineModifier;
use GetCandy\Base\CartLineModifiers;
use GetCandy\Base\CartModifier;
use GetCandy\Base\DiscountManagerInterface;
use GetCandy\Models\CartLine;
use GetCandy\Models\Discount;

class DiscountManager implements DiscountManagerInterface
{
    protected $discounts = null;

    public function addType($classname)
    {
        $this->types[] = $classname;

        return $this;
    }

    public function apply(CartLine $cartLine)
    {
        if (! $this->discounts) {
            $this->discounts = Discount::active()->orderBy('priority')->get();
        }

        foreach ($this->discounts as $discount) {
            $cartLine = $discount->type()->execute($cartLine);
        }

        return $cartLine;

//         foreach ($discounts as $discount) {
//             $type = new $discount->type($discount->data);
//
//             $modifier = $type->getModifierType();
//
//             if ($modifier == CartLineModifiers::class) {
//                 $this->cartLineModifiers->add($rule);
//             }
//         }

//         foreach ($this->types as $typeClass) {
//             $rules = app($typeClass)->getRules();
//             foreach ($rules as $rule) {
//                 if (is_subclass_of($rule, CartLineModifier::class)) {
//                     $this->cartLineModifiers->add($rule);
//                 }
//
//                 if (is_subclass_of($rule, CartModifier::class)) {
//                     $this->cartModifier->add($rule);
//                 }
//             }
//         }
    }
}
