<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\AccessControl;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\CreateStaff;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\EditStaff;
use Lunar\Admin\Filament\Resources\StaffResource\Pages\ListStaff;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Staff;
use Lunar\Filament\Schemas\Staff\StaffForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Staff\StaffTable;

class StaffResource extends BaseResource
{
    protected static ?string $permission = 'settings:manage-staff';

    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return config('lunar.staff.model', Staff::class);
    }

    public static function getLabel(): string
    {
        return __('lunar::staff.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunar::staff.plural_label');
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
        return Resolver::form(StaffForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(StaffTable::class, $table);
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
