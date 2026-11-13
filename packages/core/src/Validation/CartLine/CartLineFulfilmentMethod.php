<?php

namespace Lunar\Core\Validation\CartLine;

use Lunar\Core\Contracts\FulfilmentMethodManifest;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Validation\BaseValidator;

class CartLineFulfilmentMethod extends BaseValidator
{
    public function __construct(
        protected FulfilmentMethodManifest $fulfilmentMethods,
    ) {}

    public function validate(): bool
    {
        $key = $this->parameters['fulfilmentMethod'] ?? null;

        if ($key === null) {
            return $this->pass();
        }

        if (! $method = $this->fulfilmentMethods->get($key)) {
            return $this->fail('cart', __('lunar::exceptions.carts.fulfilment_method_unknown', ['method' => $key]));
        }

        $purchasable = $this->purchasable();

        if (! $purchasable || $this->fulfilmentMethods->supporting($purchasable)->has($key)) {
            return $this->pass();
        }

        return $this->fail('cart', __('lunar::exceptions.carts.fulfilment_method_unsupported', [
            'identifier' => $purchasable->getIdentifier(),
            'method' => $method->getLabel(),
        ]));
    }

    protected function purchasable(): ?Purchasable
    {
        if (($this->parameters['purchasable'] ?? null) instanceof Purchasable) {
            return $this->parameters['purchasable'];
        }

        $cartLine = $this->parameters['cartLine'] ?? null;

        return $cartLine instanceof CartLine ? $cartLine->purchasable : null;
    }
}
