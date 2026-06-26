<?php

namespace Lunar\Core;

use Illuminate\Support\ServiceProvider;
use Lunar\Core\Contracts\Actions as Contracts;

/**
 * Binds every action contract to its default implementation.
 *
 * This map is the canonical list of swappable action seams: a consumer
 * overrides one by binding the same contract in their own service provider.
 * Config-string substitution is intentionally not supported (spec 0016).
 * It lives in its own provider so the binding catalogue can grow without
 * bloating LunarServiceProvider.
 */
class ActionServiceProvider extends ServiceProvider
{
    /**
     * Action contract => default implementation.
     *
     * @var array<class-string, class-string>
     */
    protected array $actions = [
        // Carts
        Contracts\Carts\AddsAddress::class => Actions\Carts\AddAddress::class,
        Contracts\Carts\AddsOrUpdatesPurchasable::class => Actions\Carts\AddOrUpdatePurchasable::class,
        Contracts\Carts\AssociatesUser::class => Actions\Carts\AssociateUser::class,
        Contracts\Carts\CalculatesLine::class => Actions\Carts\CalculateLine::class,
        Contracts\Carts\CalculatesLineSubtotal::class => Actions\Carts\CalculateLineSubtotal::class,
        Contracts\Carts\CreatesOrder::class => Actions\Carts\CreateOrder::class,
        Contracts\Carts\GeneratesFingerprint::class => Actions\Carts\GenerateFingerprint::class,
        Contracts\Carts\GetsExistingCartLine::class => Actions\Carts\GetExistingCartLine::class,
        Contracts\Carts\MergesCart::class => Actions\Carts\MergeCart::class,
        Contracts\Carts\RemovesPurchasable::class => Actions\Carts\RemovePurchasable::class,
        Contracts\Carts\SetsShippingOption::class => Actions\Carts\SetShippingOption::class,
        Contracts\Carts\UpdatesCartLine::class => Actions\Carts\UpdateCartLine::class,

        // Orders
        Contracts\Orders\CancelsOrder::class => Actions\Orders\CancelOrder::class,
        Contracts\Orders\CapturesOrder::class => Actions\Orders\CaptureOrder::class,
        Contracts\Orders\ClosesOrder::class => Actions\Orders\CloseOrder::class,
        Contracts\Orders\GeneratesOrderReference::class => Actions\Orders\GenerateOrderReference::class,
        Contracts\Orders\NotifiesCustomer::class => Actions\Orders\NotifyCustomer::class,
        Contracts\Orders\RecomputesOrderStatus::class => Actions\Orders\RecomputeOrderStatus::class,
        Contracts\Orders\RefundsOrder::class => Actions\Orders\RefundOrder::class,
        Contracts\Orders\ReopensOrder::class => Actions\Orders\ReopenOrder::class,
        Contracts\Orders\ResolvesFulfilmentStatus::class => Actions\Orders\ResolveFulfilmentStatus::class,
        Contracts\Orders\ResolvesPaymentStatus::class => Actions\Orders\ResolvePaymentStatus::class,

        // Fulfilment
        Contracts\Fulfilment\AddsFulfilmentTracking::class => Actions\Fulfilment\AddFulfilmentTracking::class,
        Contracts\Fulfilment\CancelsFulfilment::class => Actions\Fulfilment\CancelFulfilment::class,
        Contracts\Fulfilment\ChangesFulfilmentLocation::class => Actions\Fulfilment\ChangeFulfilmentLocation::class,
        Contracts\Fulfilment\CreatesFulfilment::class => Actions\Fulfilment\CreateFulfilment::class,
        Contracts\Fulfilment\EnsuresInitialFulfilment::class => Actions\Fulfilment\EnsureInitialFulfilment::class,
        Contracts\Fulfilment\FulfilsFulfilment::class => Actions\Fulfilment\FulfilFulfilment::class,
        Contracts\Fulfilment\HoldsFulfilment::class => Actions\Fulfilment\HoldFulfilment::class,
        Contracts\Fulfilment\MergesFulfilments::class => Actions\Fulfilment\MergeFulfilments::class,
        Contracts\Fulfilment\MovesFulfilmentLines::class => Actions\Fulfilment\MoveFulfilmentLines::class,
        Contracts\Fulfilment\ReleasesFulfilment::class => Actions\Fulfilment\ReleaseFulfilment::class,
        Contracts\Fulfilment\RemovesFulfilmentTracking::class => Actions\Fulfilment\RemoveFulfilmentTracking::class,
        Contracts\Fulfilment\ReturnsFulfilment::class => Actions\Fulfilment\ReturnFulfilment::class,
        Contracts\Fulfilment\ShipsFulfilment::class => Actions\Fulfilment\ShipFulfilment::class,
        Contracts\Fulfilment\SplitsFulfilment::class => Actions\Fulfilment\SplitFulfilment::class,
        Contracts\Fulfilment\TransitionsFulfilment::class => Actions\Fulfilment\TransitionFulfilment::class,

        // Products
        Contracts\Products\AdjustsStock::class => Actions\Products\AdjustStock::class,
        Contracts\Products\DuplicatesProduct::class => Actions\Products\DuplicateProduct::class,
        Contracts\Products\MapsVariantsToProductOptions::class => Actions\Products\MapVariantsToProductOptions::class,
        Contracts\Products\UpdatesProductStatus::class => Actions\Products\UpdateProductStatus::class,

        // Collections
        Contracts\Collections\CreatesChildCollection::class => Actions\Collections\CreateChildCollection::class,
        Contracts\Collections\CreatesRootCollection::class => Actions\Collections\CreateRootCollection::class,
        Contracts\Collections\DeletesCollection::class => Actions\Collections\DeleteCollection::class,
        Contracts\Collections\MovesCollection::class => Actions\Collections\MoveCollection::class,
        Contracts\Collections\SortsProducts::class => Actions\Collections\SortProducts::class,

        // Currencies
        Contracts\Currencies\CreatesCurrencyPrices::class => Actions\Currencies\CreateCurrencyPrices::class,

        // Taxes
        Contracts\Taxes\GetsTaxZone::class => Actions\Taxes\GetTaxZone::class,
    ];

    public function register(): void
    {
        foreach ($this->actions as $contract => $concrete) {
            $this->app->bind($contract, $concrete);
        }
    }
}
