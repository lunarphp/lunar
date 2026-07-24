<?php

namespace Lunar\Panel\Sections\Sales;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Http\Controllers\Customers\CustomerAddressController;
use Lunar\Panel\Http\Controllers\Customers\CustomerCreateController;
use Lunar\Panel\Http\Controllers\Customers\CustomerEditController;
use Lunar\Panel\Http\Controllers\Customers\CustomerIndexController;
use Lunar\Panel\Http\Controllers\Customers\CustomerNotesController;
use Lunar\Panel\Http\Controllers\Customers\CustomerUserController;
use Lunar\Panel\Http\Controllers\EditDraftController;
use Lunar\Panel\Http\Controllers\Orders\OrderActionController;
use Lunar\Panel\Http\Controllers\Orders\OrderFulfilmentController;
use Lunar\Panel\Http\Controllers\Orders\OrderIndexController;
use Lunar\Panel\Http\Controllers\Orders\OrderShowController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Sales\Tables\CustomersTableExtension;
use Lunar\Panel\Sections\Sales\Tables\OrdersTableExtension;
use Lunar\Panel\Sections\Section;

class SalesSection extends Section
{
    /**
     * Manifest permission handle gating both the routes (via can: middleware)
     * and the navigation item, so what a user can see and what they can reach
     * stay in lockstep. Same handle as the Filament admin's CustomerResource.
     */
    private const CUSTOMERS_PERMISSION = 'sales:manage-customers';

    /** Same handle as the Filament admin's OrderResource. */
    private const ORDERS_PERMISSION = 'sales:manage-orders';

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
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
            'orders.index' => OrdersTableExtension::class,
            'customers.index' => CustomersTableExtension::class,
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
                Route::post('/{order}/fulfilments', [OrderFulfilmentController::class, 'store'])->name('fulfilments.store');

                Route::scopeBindings()->group(function (): void {
                    Route::post('/{order}/fulfilments/{fulfilment}/ship', [OrderFulfilmentController::class, 'ship'])->name('fulfilments.ship');
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
        };
    }
}
