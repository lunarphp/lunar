<?php

namespace Lunar\Core\Manifests;

use Lunar\Core\Contracts\StepManifest as StepManifestContract;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Exceptions\RemovedStepConfigException;
use Lunar\Core\Pipelines\Cart\ApplyDiscounts;
use Lunar\Core\Pipelines\Cart\ApplyShipping;
use Lunar\Core\Pipelines\Cart\Calculate;
use Lunar\Core\Pipelines\Cart\CalculateLines;
use Lunar\Core\Pipelines\Cart\CalculateShippingSubTotal;
use Lunar\Core\Pipelines\Cart\CalculateTax;
use Lunar\Core\Pipelines\CartLine\GetUnitPrice;
use Lunar\Core\Pipelines\CartPrune\PruneAfter;
use Lunar\Core\Pipelines\CartPrune\WhereNotMerged;
use Lunar\Core\Pipelines\CartPrune\WithoutOrders;
use Lunar\Core\Pipelines\Order\Creation\CleanUpOrderLines;
use Lunar\Core\Pipelines\Order\Creation\CreateOrderAddresses;
use Lunar\Core\Pipelines\Order\Creation\CreateOrderLines;
use Lunar\Core\Pipelines\Order\Creation\CreateShippingLine;
use Lunar\Core\Pipelines\Order\Creation\FillOrderFromCart;
use Lunar\Core\Pipelines\Order\Creation\MapDiscountBreakdown;
use Lunar\Core\Validation\Cart\ShippingOptionValidator;
use Lunar\Core\Validation\Cart\ValidateCartForOrderCreation;
use Lunar\Core\Validation\CartLine\CartLineAvailability;
use Lunar\Core\Validation\CartLine\CartLineQuantity;
use Lunar\Core\Validation\CartLine\CartLineStock;

class StepManifest implements StepManifestContract
{
    /**
     * The config keys that defined the lists before spec 0093.
     */
    public const REMOVED_CONFIG_KEYS = [
        'lunar.cart.pipelines',
        'lunar.cart.validators',
        'lunar.cart.prune_tables.pipelines',
        'lunar.pricing.pipelines',
        'lunar.orders.pipelines',
    ];

    /**
     * @var array<string, StepSequence>
     */
    protected array $sequences = [];

    protected bool $configChecked = false;

    /**
     * {@inheritDoc}
     */
    public function for(StepList $list): StepSequence
    {
        return $this->sequences[$list->value] ??= new StepSequence($list, $this->defaults($list), $this->guardAgainstRemovedConfig(...));
    }

    /**
     * The steps core seeds each list with, in order.
     *
     * @return list<class-string>
     */
    public function defaults(StepList $list): array
    {
        return match ($list) {
            StepList::CartCalculation => [
                CalculateLines::class,
                ApplyShipping::class,
                CalculateShippingSubTotal::class,
                ApplyDiscounts::class,
                CalculateTax::class,
                Calculate::class,
            ],
            StepList::CartLinePricing => [
                GetUnitPrice::class,
            ],
            StepList::PriceMatching => [],
            StepList::OrderCreation => [
                FillOrderFromCart::class,
                CreateOrderLines::class,
                CreateOrderAddresses::class,
                CreateShippingLine::class,
                CleanUpOrderLines::class,
                MapDiscountBreakdown::class,
            ],
            StepList::AddToCartValidation, StepList::UpdateCartLineValidation => [
                CartLineQuantity::class,
                CartLineStock::class,
                CartLineAvailability::class,
            ],
            StepList::RemoveFromCartValidation, StepList::AddAddressValidation => [],
            StepList::SetShippingOptionValidation => [
                ShippingOptionValidator::class,
            ],
            StepList::OrderCreateValidation => [
                ValidateCartForOrderCreation::class,
            ],
            StepList::CartPruning => [
                PruneAfter::class,
                WithoutOrders::class,
                WhereNotMerged::class,
            ],
        };
    }

    /**
     * Fail when a list is still defined in config, since nothing reads it any more. Runs when a
     * list is first read, not when an add-on registers against one at boot, so artisan still runs.
     *
     * @throws RemovedStepConfigException
     */
    protected function guardAgainstRemovedConfig(): void
    {
        if ($this->configChecked) {
            return;
        }

        foreach (self::REMOVED_CONFIG_KEYS as $key) {
            if (config()->has($key)) {
                throw new RemovedStepConfigException($key, array_values(array_filter(
                    StepList::cases(),
                    fn (StepList $list): bool => str_starts_with($list->legacyConfigKey(), $key),
                )));
            }
        }

        $this->configChecked = true;
    }
}
