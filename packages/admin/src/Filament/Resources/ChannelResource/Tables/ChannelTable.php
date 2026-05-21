<?php

namespace Lunar\Admin\Filament\Resources\ChannelResource\Tables;

use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Concerns\CallsHooks;

class ChannelTable
{
    use CallsHooks;

    public static function configure(Table $table): Table
    {
        return self::callStaticLunarHook(
            'configureTable',
            $table
                ->columns(static::getColumns())
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
                ]),
        );
    }

    public static function getColumns(): array
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
}
