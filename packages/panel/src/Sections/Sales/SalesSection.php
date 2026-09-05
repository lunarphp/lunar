<?php

namespace Lunar\Panel\Sections\Sales;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Panel\Contracts\DiscountTypeForm;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Http\Controllers\Customers\CustomerAddressController;
use Lunar\Panel\Http\Controllers\Customers\CustomerCreateController;
use Lunar\Panel\Http\Controllers\Customers\CustomerEditController;
use Lunar\Panel\Http\Controllers\Customers\CustomerIndexController;
use Lunar\Panel\Http\Controllers\Customers\CustomerNotesController;
use Lunar\Panel\Http\Controllers\Customers\CustomerUserController;
use Lunar\Panel\Http\Controllers\Discounts\DiscountBulkController;
use Lunar\Panel\Http\Controllers\Discounts\DiscountCreateController;
use Lunar\Panel\Http\Controllers\Discounts\DiscountEditController;
use Lunar\Panel\Http\Controllers\Discounts\DiscountIndexController;
use Lunar\Panel\Http\Controllers\Discounts\DiscountTargetSearchController;
use Lunar\Panel\Http\Controllers\EditDraftController;
use Lunar\Panel\Http\Controllers\Orders\OrderActionController;
use Lunar\Panel\Http\Controllers\Orders\OrderAddressController;
use Lunar\Panel\Http\Controllers\Orders\OrderFulfilmentController;
use Lunar\Panel\Http\Controllers\Orders\OrderIndexController;
use Lunar\Panel\Http\Controllers\Orders\OrderShowController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Search\Commands\CreateCustomerCommand;
use Lunar\Panel\Search\Commands\CreateDiscountCommand;
use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Search\Sources\CustomerSearchSource;
use Lunar\Panel\Search\Sources\OrderSearchSource;
use Lunar\Panel\Sections\Sales\Tables\CustomersTableExtension;
use Lunar\Panel\Sections\Sales\Tables\DiscountsTableExtension;
use Lunar\Panel\Sections\Sales\Tables\OrdersTableExtension;
use Lunar\Panel\Sections\Section;
use Lunar\Panel\Support\DiscountTypeForms\BuyXGetYForm;
use Lunar\Panel\Support\DiscountTypeForms\FixedAmountOffForm;
use Lunar\Panel\Support\DiscountTypeForms\PercentageOffForm;

class SalesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the navigation item, so what a user can see and what they can reach
     * stay in lockstep. Same handle as the Filament admin's CustomerResource.
     */
    public const CUSTOMERS_PERMISSION = 'sales:manage-customers';

    /** As above, and the same handle as the Filament admin's OrderResource. */
    public const ORDERS_PERMISSION = 'sales:manage-orders';

    /** As above, and the same handle the Filament admin's DiscountResource uses. */
    public const DISCOUNTS_PERMISSION = 'sales:manage-discounts';

    public function key(): string
    {
        return 'sales';
    }

    public function label(): string
    {
        return __('panel::nav.sales');
    }

    public function navigation(NavigationRegistry $registry): void
    {
        $registry->group('sales', __('panel::nav.sales'));
        $registry->addItem('sales', new NavigationItem(
            key: 'orders',
            label: __('panel::nav.orders'),
            icon: 'cart',
            route: 'panel.orders.index',
            permission: self::ORDERS_PERMISSION,
            priority: 10,
        ));
        $registry->addItem('sales', new NavigationItem(
            key: 'customers',
            label: __('panel::nav.customers'),
            icon: 'users',
            route: 'panel.customers.index',
            permission: self::CUSTOMERS_PERMISSION,
            priority: 20,
        ));
        $registry->addItem('sales', new NavigationItem(
            key: 'discounts',
            label: __('panel::nav.discounts'),
            icon: 'percent',
            route: 'panel.discounts.index',
            permission: self::DISCOUNTS_PERMISSION,
        ));
    }

    /** @return array<int, class-string<SearchSource>> */
    public function searchSources(): array
    {
        return [
            OrderSearchSource::class,
            CustomerSearchSource::class,
        ];
    }

    /** @return array<int, class-string<SearchCommand>> */
    public function searchCommands(): array
    {
        return [
            CreateCustomerCommand::class,
            CreateDiscountCommand::class,
        ];
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'orders.index' => OrdersTableExtension::class,
            'customers.index' => CustomersTableExtension::class,
            'discounts.index' => DiscountsTableExtension::class,
        ];
    }

    /** @return array<class-string, class-string<DiscountTypeForm>> */
    public function discountTypeForms(): array
    {
        return [
            PercentageOff::class => PercentageOffForm::class,
            FixedAmountOff::class => FixedAmountOffForm::class,
            BuyXGetY::class => BuyXGetYForm::class,
        ];
    }

    /** @return array<string, array<int, class-string>> */
    public function pageActions(): array
    {
        return [
            'orders.show' => [
                CloseOrderPageAction::class,
                ReopenOrderPageAction::class,
            ],
        ];
    }

    /** @return array<int, class-string<DraftableResource>> */
    public function draftables(): array
    {
        return [
            CustomerDraftResource::class,
            DiscountDraftResource::class,
        ];
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('orders')->name('panel.orders.')->middleware('can:'.self::ORDERS_PERMISSION)->group(function (): void {
                Route::get('/', [OrderIndexController::class, 'index'])->name('index');
                Route::get('/{order}', [OrderShowController::class, 'show'])->name('show');
                Route::post('/{order}/capture', [OrderActionController::class, 'capture'])->name('capture');
                Route::post('/{order}/refund', [OrderActionController::class, 'refund'])->name('refund');
                Route::post('/{order}/cancel', [OrderActionController::class, 'cancel'])->name('cancel');
                Route::post('/{order}/close', [OrderActionController::class, 'close'])->name('close');
                Route::post('/{order}/reopen', [OrderActionController::class, 'reopen'])->name('reopen');
                Route::post('/{order}/notify', [OrderActionController::class, 'notify'])->name('notify');
                Route::put('/{order}/note', [OrderActionController::class, 'note'])->name('note.update');
                Route::put('/{order}/tags', [OrderActionController::class, 'tags'])->name('tags.update');
                Route::scopeBindings()->group(function (): void {
                    Route::put('/{order}/addresses/{address}', [OrderAddressController::class, 'update'])->name('addresses.update');
                });

                Route::scopeBindings()->prefix('/{order}/fulfilments/{fulfilment}')->name('fulfilments.')->group(function (): void {
                    Route::post('/ship', [OrderFulfilmentController::class, 'ship'])->name('ship');
                    Route::post('/fulfil', [OrderFulfilmentController::class, 'fulfil'])->name('fulfil');
                    Route::post('/transition', [OrderFulfilmentController::class, 'transition'])->name('transition');
                    Route::post('/split', [OrderFulfilmentController::class, 'split'])->name('split');
                    Route::post('/merge', [OrderFulfilmentController::class, 'merge'])->name('merge');
                    Route::post('/return', [OrderFulfilmentController::class, 'markReturned'])->name('return');
                    Route::post('/undo-return', [OrderFulfilmentController::class, 'undoReturn'])->name('undo-return');
                    Route::post('/hold', [OrderFulfilmentController::class, 'hold'])->name('hold');
                    Route::post('/release', [OrderFulfilmentController::class, 'release'])->name('release');
                    Route::post('/cancel', [OrderFulfilmentController::class, 'cancel'])->name('cancel');
                    Route::put('/location', [OrderFulfilmentController::class, 'updateLocation'])->name('location.update');
                    Route::post('/trackings', [OrderFulfilmentController::class, 'storeTracking'])->name('trackings.store');
                    Route::delete('/trackings/{tracking}', [OrderFulfilmentController::class, 'destroyTracking'])->name('trackings.destroy');
                });
            });

            Route::prefix('customers')->name('panel.customers.')->middleware('can:'.self::CUSTOMERS_PERMISSION)->group(function (): void {
                Route::get('/', [CustomerIndexController::class, 'index'])->name('index');
                Route::get('/create', [CustomerCreateController::class, 'create'])->name('create');
                Route::post('/', [CustomerCreateController::class, 'store'])->name('store');
                Route::get('/{customer}/edit', [CustomerEditController::class, 'edit'])->name('edit');
                Route::put('/{customer}', [CustomerEditController::class, 'update'])->name('update');
                Route::delete('/{customer}', [CustomerEditController::class, 'destroy'])->name('destroy');
                Route::put('/{customer}/notes', [CustomerNotesController::class, 'update'])->name('notes.update');

                Route::patch('/{customer}/draft', [EditDraftController::class, 'update'])->name('draft.update');
                Route::delete('/{customer}/draft', [EditDraftController::class, 'destroy'])->name('draft.destroy');
                Route::post('/{customer}/draft/commit', [EditDraftController::class, 'commit'])->name('draft.commit');

                Route::scopeBindings()->group(function (): void {
                    Route::post('/{customer}/addresses', [CustomerAddressController::class, 'store'])->name('addresses.store');
                    Route::put('/{customer}/addresses/{address}', [CustomerAddressController::class, 'update'])->name('addresses.update');
                    Route::delete('/{customer}/addresses/{address}', [CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
                });

                Route::post('/{customer}/users', [CustomerUserController::class, 'store'])->name('users.store');
                Route::delete('/{customer}/users/{user}', [CustomerUserController::class, 'destroy'])->name('users.destroy');
            });

            Route::prefix('discounts')->name('panel.discounts.')->middleware('can:'.self::DISCOUNTS_PERMISSION)->group(function (): void {
                Route::get('/', [DiscountIndexController::class, 'index'])->name('index');
                Route::get('/create', [DiscountCreateController::class, 'create'])->name('create');
                Route::post('/', [DiscountCreateController::class, 'store'])->name('store');

                Route::post('/bulk/end', [DiscountBulkController::class, 'end'])->name('bulk-end');
                Route::post('/bulk/destroy', [DiscountBulkController::class, 'destroy'])->name('bulk-destroy');

                Route::get('/{discount}/targets/search', [DiscountTargetSearchController::class, 'search'])->name('targets.search');

                Route::get('/{discount}/edit', [DiscountEditController::class, 'edit'])->name('edit');
                Route::put('/{discount}', [DiscountEditController::class, 'update'])->name('update');
                Route::delete('/{discount}', [DiscountEditController::class, 'destroy'])->name('destroy');

                Route::patch('/{discount}/draft', [EditDraftController::class, 'update'])->name('draft.update');
                Route::delete('/{discount}/draft', [EditDraftController::class, 'destroy'])->name('draft.destroy');
                Route::post('/{discount}/draft/commit', [EditDraftController::class, 'commit'])->name('draft.commit');
            });
        };
    }
}
