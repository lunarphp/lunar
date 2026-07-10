<?php

namespace Lunar\Core;

use Illuminate\Support\ServiceProvider;
use Lunar\Core\Actions\Carts\AddAddress;
use Lunar\Core\Actions\Carts\AddOrUpdatePurchasable;
use Lunar\Core\Actions\Carts\AssociateUser;
use Lunar\Core\Actions\Carts\CalculateLine;
use Lunar\Core\Actions\Carts\CalculateLineSubtotal;
use Lunar\Core\Actions\Carts\CreateOrder;
use Lunar\Core\Actions\Carts\GenerateFingerprint;
use Lunar\Core\Actions\Carts\GetExistingCartLine;
use Lunar\Core\Actions\Carts\MergeCart;
use Lunar\Core\Actions\Carts\RemovePurchasable;
use Lunar\Core\Actions\Carts\SetShippingOption;
use Lunar\Core\Actions\Carts\UpdateCartLine;
use Lunar\Core\Actions\Collections\CreateChildCollection;
use Lunar\Core\Actions\Collections\CreateRootCollection;
use Lunar\Core\Actions\Collections\DeleteCollection;
use Lunar\Core\Actions\Collections\MoveCollection;
use Lunar\Core\Actions\Collections\SortProducts;
use Lunar\Core\Actions\Currencies\CreateCurrencyPrices;
use Lunar\Core\Actions\Customers\CreateCustomer;
use Lunar\Core\Actions\Customers\CreateCustomerAddress;
use Lunar\Core\Actions\Customers\DeleteCustomer;
use Lunar\Core\Actions\Customers\DeleteCustomerAddress;
use Lunar\Core\Actions\Customers\LinkCustomerUser;
use Lunar\Core\Actions\Customers\UnlinkCustomerUser;
use Lunar\Core\Actions\Customers\UpdateCustomer;
use Lunar\Core\Actions\Customers\UpdateCustomerAddress;
use Lunar\Core\Actions\Fulfilment\AddFulfilmentTracking;
use Lunar\Core\Actions\Fulfilment\CancelFulfilment;
use Lunar\Core\Actions\Fulfilment\ChangeFulfilmentLocation;
use Lunar\Core\Actions\Fulfilment\CreateFulfilment;
use Lunar\Core\Actions\Fulfilment\EnsureInitialFulfilment;
use Lunar\Core\Actions\Fulfilment\FulfilFulfilment;
use Lunar\Core\Actions\Fulfilment\HoldFulfilment;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\MoveFulfilmentLines;
use Lunar\Core\Actions\Fulfilment\ReleaseFulfilment;
use Lunar\Core\Actions\Fulfilment\RemoveFulfilmentTracking;
use Lunar\Core\Actions\Fulfilment\ReturnFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Actions\Fulfilment\TransitionFulfilment;
use Lunar\Core\Actions\Orders\CancelOrder;
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\Actions\Orders\CloseOrder;
use Lunar\Core\Actions\Orders\GenerateOrderReference;
use Lunar\Core\Actions\Orders\NotifyCustomer;
use Lunar\Core\Actions\Orders\RecomputeOrderStatus;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\Actions\Orders\ReopenOrder;
use Lunar\Core\Actions\Orders\ResolveFulfilmentStatus;
use Lunar\Core\Actions\Orders\ResolvePaymentStatus;
use Lunar\Core\Actions\Products\AdjustStock;
use Lunar\Core\Actions\Products\CommitReservation;
use Lunar\Core\Actions\Products\DuplicateProduct;
use Lunar\Core\Actions\Products\MapVariantsToProductOptions;
use Lunar\Core\Actions\Products\RecomputeStockReserved;
use Lunar\Core\Actions\Products\RecomputeStockRollup;
use Lunar\Core\Actions\Products\RecordStockMovement;
use Lunar\Core\Actions\Products\ReleaseReservation;
use Lunar\Core\Actions\Products\ReserveStock;
use Lunar\Core\Actions\Products\SyncStockCommitment;
use Lunar\Core\Actions\Products\UpdateProductStatus;
use Lunar\Core\Actions\Storefront\ResolveStorefrontContext;
use Lunar\Core\Actions\Taxes\GetTaxZone;
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
        Contracts\Carts\AddsAddress::class => AddAddress::class,
        Contracts\Carts\AddsOrUpdatesPurchasable::class => AddOrUpdatePurchasable::class,
        Contracts\Carts\AssociatesUser::class => AssociateUser::class,
        Contracts\Carts\CalculatesLine::class => CalculateLine::class,
        Contracts\Carts\CalculatesLineSubtotal::class => CalculateLineSubtotal::class,
        Contracts\Carts\CreatesOrder::class => CreateOrder::class,
        Contracts\Carts\GeneratesFingerprint::class => GenerateFingerprint::class,
        Contracts\Carts\GetsExistingCartLine::class => GetExistingCartLine::class,
        Contracts\Carts\MergesCart::class => MergeCart::class,
        Contracts\Carts\RemovesPurchasable::class => RemovePurchasable::class,
        Contracts\Carts\SetsShippingOption::class => SetShippingOption::class,
        Contracts\Carts\UpdatesCartLine::class => UpdateCartLine::class,

        // Orders
        Contracts\Orders\CancelsOrder::class => CancelOrder::class,
        Contracts\Orders\CapturesOrder::class => CaptureOrder::class,
        Contracts\Orders\ClosesOrder::class => CloseOrder::class,
        Contracts\Orders\GeneratesOrderReference::class => GenerateOrderReference::class,
        Contracts\Orders\NotifiesCustomer::class => NotifyCustomer::class,
        Contracts\Orders\RecomputesOrderStatus::class => RecomputeOrderStatus::class,
        Contracts\Orders\RefundsOrder::class => RefundOrder::class,
        Contracts\Orders\ReopensOrder::class => ReopenOrder::class,
        Contracts\Orders\ResolvesFulfilmentStatus::class => ResolveFulfilmentStatus::class,
        Contracts\Orders\ResolvesPaymentStatus::class => ResolvePaymentStatus::class,

        // Fulfilment
        Contracts\Fulfilment\AddsFulfilmentTracking::class => AddFulfilmentTracking::class,
        Contracts\Fulfilment\CancelsFulfilment::class => CancelFulfilment::class,
        Contracts\Fulfilment\ChangesFulfilmentLocation::class => ChangeFulfilmentLocation::class,
        Contracts\Fulfilment\CreatesFulfilment::class => CreateFulfilment::class,
        Contracts\Fulfilment\EnsuresInitialFulfilment::class => EnsureInitialFulfilment::class,
        Contracts\Fulfilment\FulfilsFulfilment::class => FulfilFulfilment::class,
        Contracts\Fulfilment\HoldsFulfilment::class => HoldFulfilment::class,
        Contracts\Fulfilment\MergesFulfilments::class => MergeFulfilments::class,
        Contracts\Fulfilment\MovesFulfilmentLines::class => MoveFulfilmentLines::class,
        Contracts\Fulfilment\ReleasesFulfilment::class => ReleaseFulfilment::class,
        Contracts\Fulfilment\RemovesFulfilmentTracking::class => RemoveFulfilmentTracking::class,
        Contracts\Fulfilment\ReturnsFulfilment::class => ReturnFulfilment::class,
        Contracts\Fulfilment\ShipsFulfilment::class => ShipFulfilment::class,
        Contracts\Fulfilment\SplitsFulfilment::class => SplitFulfilment::class,
        Contracts\Fulfilment\TransitionsFulfilment::class => TransitionFulfilment::class,

        // Products
        Contracts\Products\AdjustsStock::class => AdjustStock::class,
        Contracts\Products\CommitsReservation::class => CommitReservation::class,
        Contracts\Products\DuplicatesProduct::class => DuplicateProduct::class,
        Contracts\Products\RecomputesStockReserved::class => RecomputeStockReserved::class,
        Contracts\Products\RecomputesStockRollup::class => RecomputeStockRollup::class,
        Contracts\Products\RecordsStockMovement::class => RecordStockMovement::class,
        Contracts\Products\ReleasesReservation::class => ReleaseReservation::class,
        Contracts\Products\ReservesStock::class => ReserveStock::class,
        Contracts\Products\SyncsStockCommitment::class => SyncStockCommitment::class,
        Contracts\Products\MapsVariantsToProductOptions::class => MapVariantsToProductOptions::class,
        Contracts\Products\UpdatesProductStatus::class => UpdateProductStatus::class,

        // Collections
        Contracts\Collections\CreatesChildCollection::class => CreateChildCollection::class,
        Contracts\Collections\CreatesRootCollection::class => CreateRootCollection::class,
        Contracts\Collections\DeletesCollection::class => DeleteCollection::class,
        Contracts\Collections\MovesCollection::class => MoveCollection::class,
        Contracts\Collections\SortsProducts::class => SortProducts::class,

        // Currencies
        Contracts\Currencies\CreatesCurrencyPrices::class => CreateCurrencyPrices::class,

        // Customers
        Contracts\Customers\CreatesCustomer::class => CreateCustomer::class,
        Contracts\Customers\CreatesCustomerAddress::class => CreateCustomerAddress::class,
        Contracts\Customers\DeletesCustomer::class => DeleteCustomer::class,
        Contracts\Customers\DeletesCustomerAddress::class => DeleteCustomerAddress::class,
        Contracts\Customers\LinksCustomerUser::class => LinkCustomerUser::class,
        Contracts\Customers\UnlinksCustomerUser::class => UnlinkCustomerUser::class,
        Contracts\Customers\UpdatesCustomer::class => UpdateCustomer::class,
        Contracts\Customers\UpdatesCustomerAddress::class => UpdateCustomerAddress::class,

        // Taxes
        Contracts\Taxes\GetsTaxZone::class => GetTaxZone::class,

        // Storefront
        Contracts\Storefront\ResolvesStorefrontContext::class => ResolveStorefrontContext::class,
    ];

    public function register(): void
    {
        foreach ($this->actions as $contract => $concrete) {
            $this->app->bind($contract, $concrete);
        }
    }
}
