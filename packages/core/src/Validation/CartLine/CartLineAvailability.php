<?php

namespace Lunar\Core\Validation\CartLine;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\States\Product\Published;
use Lunar\Core\Validation\BaseValidator;

class CartLineAvailability extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        /** @var ?Cart $cart */
        $cart = $this->parameters['cart'] ?? null;
        $cartLineId = $this->parameters['cartLineId'] ?? null;
        $purchasable = $this->parameters['purchasable'] ?? null;

        if (! $purchasable && $cartLineId && $cart) {
            $purchasable = $cart->lines->first(
                fn ($cartLine) => $cartLine->id == $cartLineId
            )?->purchasable;
        }

        if (! $purchasable instanceof Purchasable) {
            return $this->pass();
        }

        if (! $purchasable instanceof ProductVariant) {
            return $purchasable->isPurchasable()
                ? $this->pass()
                : $this->failForPurchasable($purchasable);
        }

        $channel = $this->resolveChannel($cart);
        $groups = $this->resolveCustomerGroups($cart);

        if (! $channel || $groups->isEmpty()) {
            return $this->pass();
        }

        return $this->isProductPurchasable($purchasable->product_id, $channel, $groups)
            ? $this->pass()
            : $this->failForPurchasable($purchasable);
    }

    /**
     * Check whether the product can be purchased in the channel by any of
     * the given customer groups. The customer-group pivot rule here is
     * intentionally stricter than {@see HasCustomerGroups::scopeCustomerGroup},
     * which models visibility (`enabled OR visible`). Purchasability
     * additionally requires the `purchasable` pivot flag.
     */
    private function isProductPurchasable(int $productId, Channel $channel, Collection $groups): bool
    {
        $productClass = Product::class;

        return $productClass::query()
            ->where('id', $productId)
            ->where('status', Published::$name)
            ->channel($channel)
            ->whereHas('customerGroups', $this->purchasableForGroups($groups))
            ->exists();
    }

    /**
     * Build the customer-group pivot constraint for a purchasable product.
     */
    private function purchasableForGroups(Collection $groups): \Closure
    {
        $pivotTable = (new (Product::class))->customerGroups()->getTable();
        $now = now();

        return function (Builder $relation) use ($groups, $pivotTable, $now) {
            $relation->whereIn("{$pivotTable}.customer_group_id", $groups->pluck('id'))
                ->where("{$pivotTable}.enabled", true)
                ->where("{$pivotTable}.purchasable", true)
                ->where(fn ($query) => $query->whereNull("{$pivotTable}.starts_at")->orWhere("{$pivotTable}.starts_at", '<=', $now))
                ->where(fn ($query) => $query->whereNull("{$pivotTable}.ends_at")->orWhere("{$pivotTable}.ends_at", '>=', $now));
        };
    }

    private function failForPurchasable(Purchasable $purchasable): bool
    {
        return $this->fail('purchasable', __('lunar::exceptions.carts.line_not_purchasable', [
            'identifier' => $purchasable->getIdentifier(),
        ]));
    }

    private function resolveChannel(?Cart $cart): ?Channel
    {
        $channelClass = Channel::class;

        if ($cart?->channel_id) {
            return $channelClass::find($cart->channel_id);
        }

        return $channelClass::getDefault();
    }

    /**
     * @return Collection<int, CustomerGroup>
     */
    private function resolveCustomerGroups(?Cart $cart): Collection
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
