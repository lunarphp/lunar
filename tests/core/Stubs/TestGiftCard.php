<?php

namespace Lunar\Tests\Core\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lunar\Base\Purchasable;
use Lunar\DataTypes\Price;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;

/**
 * A second Purchasable model, of the kind Lunar documents as supported.
 *
 * Some behaviour is only observable with two purchasable types in play, because
 * their primary keys are independent sequences and collide as a matter of
 * course — gift card 1 and product variant 1 both exist.
 */
class TestGiftCard extends Model implements Purchasable
{
    protected $table = 'test_gift_cards';

    protected $guarded = [];

    public $timestamps = false;

    public function getPrice(): Price
    {
        return new Price(500, Currency::getDefault(), 1);
    }

    public function getPrices(): Collection
    {
        return collect([$this->getPrice()]);
    }

    public function getUnitQuantity(): int
    {
        return 1;
    }

    public function getTaxClass(): TaxClass
    {
        return TaxClass::first() ?? TaxClass::factory()->create();
    }

    public function getTaxReference(): ?string
    {
        return null;
    }

    public function getType(): string
    {
        return 'digital';
    }

    public function getDescription(): string
    {
        return $this->name ?? 'Gift card';
    }

    public function getOption(): string
    {
        return '';
    }

    public function getOptions(): Collection
    {
        return collect();
    }

    public function getIdentifier(): string
    {
        return 'GIFTCARD-'.$this->id;
    }

    public function isShippable(): bool
    {
        return false;
    }

    public function getThumbnail(): mixed
    {
        return null;
    }

    public function canBeFulfilledAtQuantity(int $quantity): bool
    {
        return true;
    }

    public function isPurchasable(): bool
    {
        return true;
    }

    public function getTotalInventory(): int
    {
        return PHP_INT_MAX;
    }
}
