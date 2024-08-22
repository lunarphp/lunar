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

class ShippingRate extends BaseModel implements Purchasable
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
    protected static function newFactory(): ShippingRateFactory
    {
        return ShippingRateFactory::new();
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
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
        return TaxClass::getDefault();
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
}
