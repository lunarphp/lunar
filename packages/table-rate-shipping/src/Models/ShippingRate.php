<?php

namespace Lunar\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Concerns\HasPrices;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Contracts\Cart as CartContract;
use Lunar\Core\Models\Contracts\TaxClass as TaxClassContract;
use Lunar\Core\Models\TaxClass;
use Lunar\Shipping\Database\Factories\ShippingRateFactory;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;

class ShippingRate extends Base implements Contracts\ShippingRate, Purchasable
{
    use HasFactory;
    use HasPrices;
    use LogsActivity;

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
        return $this->belongsTo(ShippingZone::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function getPrices(): Collection
    {
        $this->loadMissing(['prices.currency', 'prices.priceable']);

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
    public function getTaxClass(): TaxClassContract
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
    public function requiresFulfilment(): bool
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
    public function getShippingOption(CartContract $cart): ?ShippingOption
    {
        $calculateBy = config('lunar.shipping-tables.shipping_rate_tax_calculation');

        if (is_callable($calculateBy)) {
            $this->resolvedTaxClass = call_user_func($calculateBy, $cart);
        } elseif ($calculateBy == 'highest') {
            $this->resolvedTaxClass = $this->resolveHighestTaxRateInCart($cart);
        }

        $this->loadMissing('shippingMethod');

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

    public function isPurchasable(): bool
    {
        return true;
    }

    private function resolveHighestTaxRateInCart(CartContract $cart): ?TaxClass
    {
        $highestRate = false;
        $highestTaxClass = null;

        $cart->loadMissing('lines.purchasable.taxClass.taxRateAmounts');

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
