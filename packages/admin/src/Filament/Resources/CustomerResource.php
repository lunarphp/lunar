<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\OrdersRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\RelationManagers\UserRelationManager;
use Lunar\Admin\Filament\Resources\CustomerResource\Schemas\CustomerForm;
use Lunar\Admin\Filament\Resources\CustomerResource\Tables\CustomerTable;
use Lunar\Admin\Filament\Resources\CustomerResource\Widgets\CustomerStatsOverviewWidget;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Contracts\Customer as CustomerContract;

class CustomerResource extends BaseResource
{
    protected static ?string $permission = 'sales:manage-customers';

    protected static ?string $model = CustomerContract::class;

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
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerTable::configure($table);
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

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->company_name ?: $record->fullName;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'first_name',
            'last_name',
            'company_name',
            'account_ref',
            'tax_identifier',
            'users.name',
            'users.email',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with([
            'users',
        ]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Customer $record */
        $details = [
            __('lunarpanel::customer.table.full_name.label') => $record->fullName,
            __('lunarpanel::customer.table.title.label') => $record->title,
        ];

        if ($record->account_ref) {
            $details[__('lunarpanel::customer.table.account_reference.label')] = $record->account_ref;
        }

        if ($record->users() && $record->users()->count() >= 1) {
            $details[__('lunarpanel::user.table.email.label')] = $record->users()->first()->email;
        }

        return $details;
    }
}
