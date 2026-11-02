<?php

namespace Lunar\Bundles\Validation\CartLine;

use ArrayObject;
use Lunar\Bundles\Contracts\Actions\ResolvesBundleSelection;
use Lunar\Bundles\Exceptions\InvalidBundleSelection;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\ValueObjects\SelectedComponent;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Validation\BaseValidator;

/**
 * Rejects an add or update whose meta names a selection the bundle cannot
 * satisfy, and checks every selected component's stock for the actual
 * selection (the inventory seam answers for the best case only).
 */
class BundleSelection extends BaseValidator
{
    public function __construct(
        protected ResolvesBundleSelection $resolver,
    ) {}

    public function validate(): bool
    {
        $cart = $this->parameters['cart'] ?? null;
        $purchasable = $this->parameters['purchasable'] ?? null;
        $cartLineId = $this->parameters['cartLineId'] ?? null;
        $quantity = (int) ($this->parameters['quantity'] ?? 0);
        $line = null;

        if (! $purchasable && $cartLineId && $cart) {
            /** @var ?CartLine $line */
            $line = $cart->lines->first(fn (CartLine $cartLine) => $cartLine->id == $cartLineId);
            $purchasable = $line?->purchasable;
        }

        if (! $purchasable instanceof ProductVariant) {
            return $this->pass();
        }

        /** @var ?Bundle $bundle */
        $bundle = $purchasable->loadMissing('bundle')->bundle;

        if (! $bundle) {
            return $this->pass();
        }

        try {
            $selection = $this->resolver->execute($bundle, $this->metaFor($line));
        } catch (InvalidBundleSelection $exception) {
            return $this->fail('cart', $exception->getMessage());
        }

        foreach ($selection as $component) {
            /** @var SelectedComponent $component */
            if (! $component->variant->canBeFulfilledAtQuantity($component->quantity * $quantity)) {
                return $this->fail('cart', __('bundles::bundles.selection.unavailable', [
                    'identifier' => $component->variant->getIdentifier(),
                ]));
            }
        }

        return $this->pass();
    }

    /**
     * The meta the line will carry after the action runs: `UpdateCartLine`
     * replaces the line's meta when a non-empty one is given, so an update
     * with no meta is validated against what the line already holds.
     *
     * @return array<string, mixed>
     */
    protected function metaFor(?CartLine $line): array
    {
        $incoming = $this->parameters['meta'] ?? null;

        if ($incoming instanceof ArrayObject) {
            $incoming = $incoming->getArrayCopy();
        }

        if (is_array($incoming) && $incoming !== []) {
            return $incoming;
        }

        $current = $line?->meta;

        return $current instanceof ArrayObject ? $current->getArrayCopy() : (array) $current;
    }
}
