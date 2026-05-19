<?php

namespace Lunar\Validation\CartLine;

use Illuminate\Support\Collection;
use Lunar\Base\Purchasable;
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
                : $this->fail('purchasable', __('lunar::exceptions.carts.line_not_purchasable', [
                    'identifier' => $purchasable->getIdentifier(),
                ]));
        }

        $channel = $this->resolveChannel($cart);
        $groups = $this->resolveCustomerGroups($cart);

        if (! $channel || $groups->isEmpty()) {
            return $this->pass();
        }

        $productClass = Product::modelClass();
        $pivotTable = (new $productClass)->customerGroups()->getTable();
        $now = now();

        $available = $productClass::query()
            ->where('id', $purchasable->product_id)
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
