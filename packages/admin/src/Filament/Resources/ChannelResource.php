<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\CreateChannel;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\EditChannel;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\ListChannels;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Core\Models\Channel;
use Lunar\Filament\Schemas\Channel\ChannelForm;
use Lunar\Filament\Support\Resolver;
use Lunar\Filament\Tables\Channel\ChannelTable;

class ChannelResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = Channel::class;

    protected static ?int $navigationSort = 1;

    public static function getLabel(): string
    {
        return __('lunarpanel::channel.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel::channel.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::channels');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.settings');
    }

    public static function form(Schema $schema): Schema
    {
        return Resolver::form(ChannelForm::class, $schema);
    }

    public static function table(Table $table): Table
    {
        return Resolver::table(ChannelTable::class, $table);
    }

    protected static function getDefaultPages(): array
    {
        return [
            'index' => ListChannels::route('/'),
            'create' => CreateChannel::route('/create'),
            'edit' => EditChannel::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
