<?php

namespace Lunar\Validation\CartLine;

use Illuminate\Support\Collection;
use Lunar\Models\Channel;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;
use Lunar\Validation\BaseValidator;

class CartLineAvailability extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        /** @var ?CartContract $cart */
        $cart = $this->parameters['cart'] ?? null;
        $cartLineId = $this->parameters['cartLineId'] ?? null;
        $purchasable = $this->parameters['purchasable'] ?? null;

        if (! $purchasable && $cartLineId && $cart) {
            $purchasable = $cart->lines->first(
                fn ($cartLine) => $cartLine->id == $cartLineId
            )?->purchasable;
        }

        if (! $purchasable instanceof ProductVariantContract) {
            return $this->pass();
        }

        $channel = $this->resolveChannel($cart);
        $groups = $this->resolveCustomerGroups($cart);

        if (! $channel || $groups->isEmpty()) {
            return $this->pass();
        }

        $pivotTable = (new Product)->customerGroups()->getTable();
        $now = now();

        $available = Product::query()
            ->whereKey($purchasable->product_id)
            ->channel($channel)
            ->whereHas('customerGroups', function ($relation) use ($groups, $pivotTable, $now) {
                $relation->whereIn("{$pivotTable}.customer_group_id", $groups->pluck('id'))
                    ->where("{$pivotTable}.enabled", true)
                    ->where("{$pivotTable}.purchasable", true)
                    ->where(fn ($query) => $query->whereNull("{$pivotTable}.starts_at")->orWhere("{$pivotTable}.starts_at", '<=', $now))
                    ->where(fn ($query) => $query->whereNull("{$pivotTable}.ends_at")->orWhere("{$pivotTable}.ends_at", '>=', $now));
            })
            ->exists();

        if (! $available) {
            return $this->fail('purchasable', __('lunar::exceptions.carts.line_not_purchasable', [
                'identifier' => $purchasable->getIdentifier(),
            ]));
        }

        return $this->pass();
    }

    private function resolveChannel(?CartContract $cart): ?Channel
    {
        if ($cart?->channel_id) {
            return Channel::find($cart->channel_id);
        }

        return Channel::getDefault();
    }

    /**
     * @return Collection<int, \Lunar\Models\Contracts\CustomerGroup>
     */
    private function resolveCustomerGroups(?CartContract $cart): Collection
    {
        if ($cart?->customer) {
            $groups = $cart->customer->customerGroups;

            if ($groups->isNotEmpty()) {
                return $groups;
            }
        }

        $default = CustomerGroup::getDefault();

        return $default ? new Collection([$default]) : new Collection;
    }
}
