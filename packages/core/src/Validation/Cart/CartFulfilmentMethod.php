<?php

namespace Lunar\Core\Validation\Cart;

use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Validation\BaseValidator;

/**
 * The cart default is a preference, so any registered method is accepted;
 * lines it can't fulfil skip it.
 */
class CartFulfilmentMethod extends BaseValidator
{
    public function __construct(
        protected FulfilmentMethodManifest $fulfilmentMethods,
    ) {}

    public function validate(): bool
    {
        $key = $this->parameters['fulfilmentMethod'] ?? null;

        if ($key === null || $this->fulfilmentMethods->get($key)) {
            return $this->pass();
        }

        return $this->fail('cart', __('lunar::exceptions.carts.fulfilment_method_unknown', ['method' => $key]));
    }
}
