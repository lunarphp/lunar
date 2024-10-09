<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lunar\Base\BaseModel;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasPrices;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Database\Factories\ShippingRateFactory;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;

class ShippingRate extends BaseModel implements Contracts\ShippingRate, Purchasable
{
    use HasFactory;
    use HasPrices;

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    private ?TaxClass $resolvedTaxClass;

    protected static function booted()
    {
        self::deleting(function (self $shippingRate) {
            DB::beginTransaction();
            $shippingRate->prices()->delete();
            DB::commit();
        });
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ShippingRateFactory::new();
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::modelClass());
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::modelClass());
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * Return the unit quantity for the variant.
     */
    public function getUnitQuantity(): int
    {
        return 1;
    }

    /**
     * Return the tax class.
     */
    public function getTaxClass(): TaxClass
    {
        return $this->resolvedTaxClass ?? TaxClass::getDefault();
    }

    public function getTaxReference(): ?string
    {
        return $this->shippingMethod->code;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return 'shipping';
    }

    /**
     * {@inheritDoc}
     */
    public function isShippable(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): ?string
    {
        return $this->shippingMethod->name ?: $this->driver()->name();
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(): ?string
    {
        return $this->shippingMethod->code;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): Collection
    {
        return collect();
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): ?string
    {
        return $this->shippingMethod->code;
    }

    public function getThumbnail(): ?string
    {
        return null;
    }

    /**
     * Return the shipping method driver.
     */
    public function getShippingOption(Cart $cart): ?ShippingOption
    {
        if (config('lunar.shipping-tables.shipping_rate_tax_calculation') == 'highest') {
            $this->resolvedTaxClass = $this->resolveHighestTaxRateInCart($cart);
        }

        return $this->shippingMethod->driver()->resolve(
            new ShippingOptionRequest(
                shippingRate: $this,
                cart: $cart,
            )
        );
    }

    public function canBeFulfilledAtQuantity(int $quantity): bool
    {
        return true;
    }

    public function getTotalInventory(): int
    {
        return 1;
    }

    private function resolveHighestTaxRateInCart(Cart $cart): ?TaxClass
    {
        $highestRate = false;
        $highestTaxClass = null;

        foreach ($cart->lines as $cartLine) {
            if ($cartLine->purchasable->taxClass) {
                foreach ($cartLine->purchasable->taxClass->taxRateAmounts as $amount) {
                    if ($highestRate === false || $amount->percentage > $highestRate) {
                        $highestRate = $amount->percentage;
                        $highestTaxClass = $cartLine->purchasable->taxClass;
                    }
                }
            }
        }

        return $highestTaxClass;
    }
}
