<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\AccessControl;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\CreateStaff;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\EditStaff;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\ListStaff;
use Lunar\Admin\Filament\Resources\StaffResource\Schemas\StaffForm;
use Lunar\Admin\Filament\Resources\StaffResource\Tables\StaffTable;
use Lunar\Admin\Models\Staff;
use Lunar\Admin\Support\Resources\BaseResource;

class StaffResource extends BaseResource
{
    protected static ?string $permission = 'settings:manage-staff';

    protected static ?string $model = Staff::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::staff.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::staff.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::staff');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListStaff::route('/'),
            'acl' => AccessControl::route('/access-control'),
            'create' => CreateStaff::route('/create'),
            'edit' => EditStaff::route('/{record}/edit'),
        ];
    }
}
