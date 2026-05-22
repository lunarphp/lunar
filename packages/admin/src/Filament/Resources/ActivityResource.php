<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Lunar\Admin\Filament\Resources\ActivityResource\Pages\ListActivities;
use Lunar\Admin\Filament\Resources\ActivityResource\Pages\ViewActivity;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Filament\Schemas\Activity\ActivityForm;
use Lunar\Filament\Tables\Activity\ActivityTable;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends BaseResource
{
    protected static ?string $permission = 'settings';

    protected static ?string $model = Activity::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::activity.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::activity.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::activity');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityTable::configure($table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }
}
