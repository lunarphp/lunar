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
use Lunar\Panel\Navigation\NavigationItem;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Sections\Sales\Tables\CustomersTableExtension;
use Lunar\Panel\Sections\Sales\Tables\DiscountsTableExtension;
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
    private const CUSTOMERS_PERMISSION = 'sales:manage-customers';

    /** As above, and the same handle the Filament admin's DiscountResource uses. */
    private const DISCOUNTS_PERMISSION = 'sales:manage-discounts';

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
            key: 'customers',
            label: __('panel::nav.customers'),
            icon: 'users',
            route: 'panel.customers.index',
            permission: self::CUSTOMERS_PERMISSION,
        ));
        $registry->addItem('sales', new NavigationItem(
            key: 'discounts',
            label: __('panel::nav.discounts'),
            icon: 'percent',
            route: 'panel.discounts.index',
            permission: self::DISCOUNTS_PERMISSION,
        ));
    }

    /** @return array<string, class-string> */
    public function tableExtensions(): array
    {
        return [
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
