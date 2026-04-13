<?php

namespace Lunar\Admin\Filament\Resources;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\CreateChannel;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\EditChannel;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\ListChannels;
use Lunar\Admin\Support\Resources\BaseResource;
use Lunar\Models\Contracts\Channel as ChannelContract;

class ChannelResource extends BaseResource
{
    protected static ?string $permission = 'settings:core';

    protected static ?string $model = ChannelContract::class;

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
        return TextInput::make('name')
            ->label(__('lunarpanel::channel.form.name.label'))
            ->required()
            ->maxLength(255)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state));
            })
            ->live(onBlur: true)
            ->autofocus();
    }

    protected static function getHandleFormComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunarpanel::channel.form.handle.label'))
            ->required()
            ->unique(ignoreRecord: true)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }

                $set('handle', Str::snake(Str::lower($state)));
            })
            ->minLength(3)
            ->maxLength(255);
    }

    protected static function getUrlFormComponent(): Component
    {
        return TextInput::make('url')
            ->label(__('lunarpanel::channel.form.url.label'))
            ->maxLength(255)
            ->autofocus();
    }

    protected static function getDefaultFormComponent(): Component
    {
        return Toggle::make('default')
            ->label(__('lunarpanel::channel.form.default.label'));
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTableColumns(): array
    {
        return [
            BadgeableColumn::make('name')
                ->separator('')
                ->suffixBadges([
                    Badge::make('default')
                        ->label(__('lunarpanel::channel.table.default.label'))
                        ->color('gray')
                        ->visible(fn (Model $record) => $record->default),
                ])
                ->label(__('lunarpanel::channel.table.name.label')),
            TextColumn::make('handle')
                ->label(__('lunarpanel::channel.table.handle.label')),
            TextColumn::make('url')
                ->label(__('lunarpanel::channel.table.url.label')),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getDefaultPages(): array
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
