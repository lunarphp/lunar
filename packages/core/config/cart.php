<?php

use Lunar\Actions\Carts\AddAddress;
use Lunar\Actions\Carts\AddOrUpdatePurchasable;
use Lunar\Actions\Carts\CreateOrder;
use Lunar\Actions\Carts\GenerateFingerprint;
use Lunar\Actions\Carts\GetExistingCartLine;
use Lunar\Actions\Carts\RemovePurchasable;
use Lunar\Actions\Carts\SetShippingOption;
use Lunar\Actions\Carts\UpdateCartLine;
use Lunar\Pipelines\Cart\ApplyDiscounts;
use Lunar\Pipelines\Cart\ApplyShipping;
use Lunar\Pipelines\Cart\Calculate;
use Lunar\Pipelines\Cart\CalculateLines;
use Lunar\Pipelines\Cart\CalculateTax;
use Lunar\Pipelines\CartLine\GetUnitPrice;
use Lunar\Pipelines\CartPrune\PruneAfter;
use Lunar\Pipelines\CartPrune\WhereNotMerged;
use Lunar\Pipelines\CartPrune\WithoutOrders;
use Lunar\Validation\Cart\ShippingOptionValidator;
use Lunar\Validation\Cart\ValidateCartForOrderCreation;
use Lunar\Validation\CartLine\CartLineQuantity;
use Lunar\Validation\CartLine\CartLineStock;

return [
    /*
    |--------------------------------------------------------------------------
    | Fingerprint Generator
    |--------------------------------------------------------------------------
    |
    | Specify which class should be used when generating a cart fingerprint.
    |
    */
    'fingerprint_generator' => GenerateFingerprint::class,

    /*
    |--------------------------------------------------------------------------
    | Authentication policy
    |--------------------------------------------------------------------------
    |
    | When a user logs in, by default, Lunar will merge the current (guest) cart
    | with the users current cart, if they have one.
    | Available options: 'merge', 'override'
    |
    */
    'auth_policy' => 'merge',

    /*
    |--------------------------------------------------------------------------
    | Cart Pipelines
    |--------------------------------------------------------------------------
    |
    | Define which pipelines should be run when performing cart calculations.
    | The default ones provided should suit most needs, however you are
    | free to add your own as you see fit.
    |
    | Each pipeline class will be run from top to bottom.
    |
    */
    'pipelines' => [
        /*
         * Run these pipelines when the cart is calculating.
        */
        'cart' => [
            CalculateLines::class,
            ApplyShipping::class,
            ApplyDiscounts::class,
            CalculateTax::class,
            Calculate::class,
        ],

        /*
         * Run these pipelines when the cart lines are being calculated.
        */
        'cart_lines' => [
            GetUnitPrice::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Actions
    |--------------------------------------------------------------------------
    |
    | Here you can decide what action should be run during a Carts lifecycle.
    | The default actions should be fine for most cases.
    |
    */
    'actions' => [
        'add_to_cart' => AddOrUpdatePurchasable::class,
        'get_existing_cart_line' => GetExistingCartLine::class,
        'update_cart_line' => UpdateCartLine::class,
        'remove_from_cart' => RemovePurchasable::class,
        'add_address' => AddAddress::class,
        'set_shipping_option' => SetShippingOption::class,
        'order_create' => CreateOrder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Action Validators
    |--------------------------------------------------------------------------
    |
    | You may wish to provide additional validation when actions executed on
    | the cart model. The defaults provided should be enough for most cases.
    |
    */
    'validators' => [

        'add_to_cart' => [
            CartLineQuantity::class,
            CartLineStock::class,
        ],

        'update_cart_line' => [
            CartLineQuantity::class,
            CartLineStock::class,
        ],

        'remove_from_cart' => [],

        'set_shipping_option' => [
            ShippingOptionValidator::class,
        ],

        'order_create' => [
            ValidateCartForOrderCreation::class,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default eager loading
    |--------------------------------------------------------------------------
    |
    | When loading up a cart and doing calculations, there's a few relationships
    | that are used when it's running. Here you can define which relationships
    | should be eager loaded when these calculations take place.
    |
    */
    'eager_load' => [
        'currency',
        'lines.purchasable.taxClass',
        'lines.purchasable.values',
        'lines.purchasable.product.thumbnail',
        'lines.purchasable.prices.currency',
        'lines.purchasable.prices.priceable',
        'lines.purchasable.product',
        'lines.cart.currency',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prune carts
    |--------------------------------------------------------------------------
    |
    | Should the cart models be pruned to prevent data build up and
    | some settings controlling how pruning should be determined
    |
    */
    'prune_tables' => [

        'enabled' => false,

        'pipelines' => [
            PruneAfter::class,
            WithoutOrders::class,
            WhereNotMerged::class,
        ],

        'prune_interval' => 90, // days

    ],
];
