<?php

namespace Lunar\Panel\Sections\Sales;

use Closure;
use Illuminate\Support\Facades\Route;
use Lunar\Panel\Http\Controllers\Customers\CustomerAddressController;
use Lunar\Panel\Http\Controllers\Customers\CustomerCreateController;
use Lunar\Panel\Http\Controllers\Customers\CustomerEditController;
use Lunar\Panel\Http\Controllers\Customers\CustomerIndexController;
use Lunar\Panel\Http\Controllers\Customers\CustomerUserController;
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Section;

class SalesSection extends Section
{
    public function key(): string
    {
        return 'sales';
    }

    public function label(): string
    {
        return 'Sales';
    }

    public function navigation(NavigationRegistry $registry): void
    {
        $registry->group('sales', 'Sales');
        $registry->addItem('sales', new NavigationItem(
            key: 'customers',
            label: 'Customers',
            icon: 'users',
            route: 'panel.customers.index',
        ));
    }

    public function routes(): ?Closure
    {
        return function (): void {
            Route::prefix('customers')->name('panel.customers.')->group(function (): void {
                Route::get('/', [CustomerIndexController::class, 'index'])->name('index');
                Route::get('/create', [CustomerCreateController::class, 'create'])->name('create');
                Route::post('/', [CustomerCreateController::class, 'store'])->name('store');
                Route::get('/{customer}/edit', [CustomerEditController::class, 'edit'])->name('edit');
                Route::put('/{customer}', [CustomerEditController::class, 'update'])->name('update');
                Route::delete('/{customer}', [CustomerEditController::class, 'destroy'])->name('destroy');

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
