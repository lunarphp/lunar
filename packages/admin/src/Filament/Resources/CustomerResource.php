<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Customer;
use Lunar\Filament\GlobalSearch\Concerns\HasLunarGlobalSearch;
use Lunar\Filament\GlobalSearch\CustomerGlobalSearch;
use Lunar\Filament\RelationManagers\Customer\AddressRelationManager;
use Lunar\Filament\RelationManagers\Customer\OrdersRelationManager;
use Lunar\Filament\RelationManagers\Customer\UserRelationManager;
use Lunar\Filament\Schemas\Customer\CustomerForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Customer\CustomerTable;
use Lunar\Filament\Widgets\Customer\CustomerStatsOverviewWidget;

class CustomerResource extends BaseResource
{
    use HasLunarGlobalSearch;

    protected static string $globalSearch = CustomerGlobalSearch::class;

    protected static ?string $permission = 'sales:manage-customers';

    protected static ?string $model = Customer::class;

    protected static ?int $navigationSort = 2;

    protected static int $globalSearchResultsLimit = 5;

    public static function getLabel(): string
    {
        return __('lunarpanel::customer.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::customer.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::customers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.sales');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(CustomerForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(CustomerTable::class, $table);
    }

    public static function getWidgets(): array
    {
        return [
            CustomerStatsOverviewWidget::class,
        ];
    }

    protected static function getDefaultRelations(): array
    {
        return [
            OrdersRelationManager::class,
            AddressRelationManager::class,
            UserRelationManager::class,
        ];
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
