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
use Lunar\Core\Actions\Orders\CaptureOrder;
use Lunar\Core\Actions\Orders\GenerateOrderReference;
use Lunar\Core\Actions\Orders\MarkOrderAsShipped;
use Lunar\Core\Actions\Orders\RefundOrder;
use Lunar\Core\Actions\Orders\UpdateOrderStatus;
use Lunar\Core\Actions\Products\AdjustStock;
use Lunar\Core\Actions\Products\DuplicateProduct;
use Lunar\Core\Actions\Products\MapVariantsToProductOptions;
use Lunar\Core\Actions\Products\UpdateProductStatus;
use Lunar\Core\Actions\Taxes\GetTaxZone;
use Lunar\Core\Contracts\Actions\Carts\AddsAddress;
use Lunar\Core\Contracts\Actions\Carts\AddsOrUpdatesPurchasable;
use Lunar\Core\Contracts\Actions\Carts\AssociatesUser;
use Lunar\Core\Contracts\Actions\Carts\CalculatesLine;
use Lunar\Core\Contracts\Actions\Carts\CalculatesLineSubtotal;
use Lunar\Core\Contracts\Actions\Carts\CreatesOrder;
use Lunar\Core\Contracts\Actions\Carts\GeneratesFingerprint;
use Lunar\Core\Contracts\Actions\Carts\GetsExistingCartLine;
use Lunar\Core\Contracts\Actions\Carts\MergesCart;
use Lunar\Core\Contracts\Actions\Carts\RemovesPurchasable;
use Lunar\Core\Contracts\Actions\Carts\SetsShippingOption;
use Lunar\Core\Contracts\Actions\Carts\UpdatesCartLine;
use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Contracts\Actions\Collections\CreatesRootCollection;
use Lunar\Core\Contracts\Actions\Collections\DeletesCollection;
use Lunar\Core\Contracts\Actions\Collections\MovesCollection;
use Lunar\Core\Contracts\Actions\Collections\SortsProducts;
use Lunar\Core\Contracts\Actions\Currencies\CreatesCurrencyPrices;
use Lunar\Core\Contracts\Actions\Orders\CapturesOrder;
use Lunar\Core\Contracts\Actions\Orders\GeneratesOrderReference;
use Lunar\Core\Contracts\Actions\Orders\MarksOrderAsShipped;
use Lunar\Core\Contracts\Actions\Orders\RefundsOrder;
use Lunar\Core\Contracts\Actions\Orders\UpdatesOrderStatus;
use Lunar\Core\Contracts\Actions\Products\AdjustsStock;
use Lunar\Core\Contracts\Actions\Products\DuplicatesProduct;
use Lunar\Core\Contracts\Actions\Products\MapsVariantsToProductOptions;
use Lunar\Core\Contracts\Actions\Products\UpdatesProductStatus;
use Lunar\Core\Contracts\Actions\Taxes\GetsTaxZone;

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
        AddsAddress::class => AddAddress::class,
        AddsOrUpdatesPurchasable::class => AddOrUpdatePurchasable::class,
        AssociatesUser::class => AssociateUser::class,
        CalculatesLine::class => CalculateLine::class,
        CalculatesLineSubtotal::class => CalculateLineSubtotal::class,
        CreatesOrder::class => CreateOrder::class,
        GeneratesFingerprint::class => GenerateFingerprint::class,
        GetsExistingCartLine::class => GetExistingCartLine::class,
        MergesCart::class => MergeCart::class,
        RemovesPurchasable::class => RemovePurchasable::class,
        SetsShippingOption::class => SetShippingOption::class,
        UpdatesCartLine::class => UpdateCartLine::class,

        // Orders
        CapturesOrder::class => CaptureOrder::class,
        GeneratesOrderReference::class => GenerateOrderReference::class,
        MarksOrderAsShipped::class => MarkOrderAsShipped::class,
        RefundsOrder::class => RefundOrder::class,
        UpdatesOrderStatus::class => UpdateOrderStatus::class,

        // Products
        AdjustsStock::class => AdjustStock::class,
        DuplicatesProduct::class => DuplicateProduct::class,
        MapsVariantsToProductOptions::class => MapVariantsToProductOptions::class,
        UpdatesProductStatus::class => UpdateProductStatus::class,

        // Collections
        CreatesChildCollection::class => CreateChildCollection::class,
        CreatesRootCollection::class => CreateRootCollection::class,
        DeletesCollection::class => DeleteCollection::class,
        MovesCollection::class => MoveCollection::class,
        SortsProducts::class => SortProducts::class,

        // Currencies
        CreatesCurrencyPrices::class => CreateCurrencyPrices::class,

        // Taxes
        GetsTaxZone::class => GetTaxZone::class,
    ];

    public function register(): void
    {
        foreach ($this->actions as $contract => $concrete) {
            $this->app->bind($contract, $concrete);
        }
    }
}
