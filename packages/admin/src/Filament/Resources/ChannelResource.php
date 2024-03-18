<?php

namespace Lunar\Admin\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Channel;

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

    protected static function getMainFormComponents(): array
    {
        return [
            static::getNameFormComponent(),
            static::getHandleFormComponent(),
            static::getUrlFormComponent(),
            static::getDefaultFormComponent(),
        ];
    }

    protected static function getNameFormComponent(): Component
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('lunarpanel::channel.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return Forms\Components\TextInput::make('handle')
            ->label(__('lunarpanel::channel.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->minLength(3)
            ->maxLength(255);
    }

    protected static function getUrlFormComponent(): Component
    {
        return Forms\Components\TextInput::make('url')
            ->label(__('lunarpanel::channel.form.url.label'))
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Forms\Components\Toggle::make('default')
            ->label(__('lunarpanel::channel.form.default.label'));
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('lunarpanel::channel.table.name.label')),
            Tables\Columns\TextColumn::make('handle')
                ->label(__('lunarpanel::channel.table.handle.label')),
            Tables\Columns\TextColumn::make('url')
                ->label(__('lunarpanel::channel.table.url.label')),
            Tables\Columns\BooleanColumn::make('default')
                ->label(__('lunarpanel::channel.table.default.label')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
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
