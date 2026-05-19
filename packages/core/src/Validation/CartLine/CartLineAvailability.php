<?php

namespace Lunar\Validation\CartLine;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasCustomerGroups;
use Lunar\Models\Channel;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\Channel as ChannelContract;
use Lunar\Models\Contracts\CustomerGroup as CustomerGroupContract;
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

        if (! $purchasable instanceof Purchasable) {
            return $this->pass();
        }

        if (! $purchasable instanceof ProductVariantContract) {
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
    private function isProductPurchasable(int $productId, ChannelContract $channel, Collection $groups): bool
    {
        $productClass = Product::modelClass();

        return $productClass::query()
            ->where('id', $productId)
            ->channel($channel)
            ->whereHas('customerGroups', $this->purchasableForGroups($groups))
            ->exists();
    }

    /**
     * Build the customer-group pivot constraint for a purchasable product.
     */
    private function purchasableForGroups(Collection $groups): \Closure
    {
        $pivotTable = (new (Product::modelClass()))->customerGroups()->getTable();
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

    private function resolveChannel(?CartContract $cart): ?ChannelContract
    {
        $channelClass = Channel::modelClass();

        if ($cart?->channel_id) {
            return $channelClass::find($cart->channel_id);
        }

        return $channelClass::getDefault();
    }

    /**
     * @return Collection<int, CustomerGroupContract>
     */
    private function resolveCustomerGroups(?CartContract $cart): Collection
    {
        if ($cart?->customer) {
            $groups = $cart->customer->customerGroups;

            if ($groups->isNotEmpty()) {
                return $groups;
            }
        }

        $default = CustomerGroup::modelClass()::getDefault();

        return $default ? new Collection([$default]) : new Collection;
    }
}
