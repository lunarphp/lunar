<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages\CreateCustomerGroup;
use Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages\EditCustomerGroup;
use Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages\ListCustomerGroups;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Filament\Schemas\CustomerGroup\CustomerGroupForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\CustomerGroup\CustomerGroupTable;

class CustomerGroupResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = CustomerGroup::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::customergroup.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::customergroup.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::customer-groups');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(CustomerGroupForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(CustomerGroupTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListCustomerGroups::route('/'),
            'create' => CreateCustomerGroup::route('/create'),
            'edit' => EditCustomerGroup::route('/{record}/edit'),
        ];
    }
}
